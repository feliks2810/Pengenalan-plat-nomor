#!/usr/bin/env python3
# Simpan script ini di: {project_laravel}/python/helmet_detection.py

import cv2
import numpy as np
import os
import datetime
import csv
import sys
import time
from ultralytics import YOLO
from easyocr import Reader

def main():
    if len(sys.argv) < 3:
        print("Usage: python helmet_detection.py [model_path] [storage_dir]")
        sys.exit(1)
    
    # Ambil parameter dari command line
    model_path = sys.argv[1]
    storage_dir = sys.argv[2]
    csv_file = os.path.join(os.path.dirname(storage_dir), 'pelanggaran.csv')
    
    # Inisialisasi model
    model = YOLO(model_path)
    reader = Reader(['en'], gpu=False)
    
    # Buat file CSV jika belum ada
    if not os.path.exists(csv_file):
        with open(csv_file, 'w', newline='') as f:
            writer = csv.writer(f)
            writer.writerow(['waktu', 'plat_nomor', 'gambar'])
    
    # Untuk mencegah deteksi berulang
    last_violation_time = 0
    min_time_between_detections = 5  # detik
    
    # Buka kamera
    cap = cv2.VideoCapture(0)
    cap.set(cv2.CAP_PROP_BUFFERSIZE, 1)
    cap.set(cv2.CAP_PROP_FRAME_WIDTH, 640)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 480)
    
    while True:
        # Baca frame dari kamera
        success, frame = cap.read()
        if not success:
            empty_frame = np.zeros((480, 640, 3), dtype=np.uint8)
            cv2.putText(empty_frame, "Camera Error", (50, 240), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)
            _, buffer = cv2.imencode('.jpg', empty_frame)
            sys.stdout.buffer.write(b'--frame\r\n')
            sys.stdout.buffer.write(b'Content-Type: image/jpeg\r\n\r\n')
            sys.stdout.buffer.write(buffer.tobytes())
            sys.stdout.buffer.write(b'\r\n')
            sys.stdout.flush()
            time.sleep(0.5)
            continue
        
        # Jalankan deteksi
        results = model(frame)[0]
        current_time = time.time()
        
        # Process results
        for box in results.boxes:
            x1, y1, x2, y2 = box.xyxy[0].cpu().numpy().astype(int)
            conf = box.conf[0].item()
            cls = int(box.cls[0])
            label = model.names.get(cls, f"id:{cls}")
            
            # Gambar kotak deteksi
            cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)
            cv2.putText(frame, f'{label} {conf:.2f}', (x1, y1 - 10),
                       cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 255, 0), 2)
            
            # Cek pelanggaran helm
            if label.lower() == 'no helm' and conf > 0.6:
                # Cek jika cukup waktu sejak pelanggaran terakhir
                if current_time - last_violation_time > min_time_between_detections:
                    last_violation_time = current_time
                    
                    # Simpan gambar pelanggaran
                    timestamp = datetime.datetime.now().strftime('%Y%m%d_%H%M%S')
                    filename = f'pelanggaran_{timestamp}.jpg'
                    filepath = os.path.join(storage_dir, filename)
                    cv2.imwrite(filepath, frame)
                    
                    # OCR untuk plat nomor
                    try:
                        height, width = frame.shape[:2]
                        roi = frame[int(height/2):height, 0:width]
                        
                        ocr_result = reader.readtext(roi)
                        plate_text = ''
                        if ocr_result:
                            for res in ocr_result:
                                if len(res[1]) > 1:  # Skip single character results
                                    plate_text += res[1] + " "
                            plate_text = plate_text.strip()
                    except Exception as e:
                        plate_text = ''
                    
                    # Simpan ke CSV
                    with open(csv_file, 'a', newline='') as f:
                        writer = csv.writer(f)
                        writer.writerow([timestamp, plate_text, filename])
        
        # Kirim frame sebagai bagian dari MJPEG stream
        _, buffer = cv2.imencode('.jpg', frame)
        sys.stdout.buffer.write(b'--frame\r\n')
        sys.stdout.buffer.write(b'Content-Type: image/jpeg\r\n\r\n')
        sys.stdout.buffer.write(buffer.tobytes())
        sys.stdout.buffer.write(b'\r\n')
        sys.stdout.flush()
        
        # Target 20fps untuk performa lebih baik
        time.sleep(0.05)

if __name__ == '__main__':
    try:
        main()
    except KeyboardInterrupt:
        print("Detection stopped.")
        sys.exit(0)