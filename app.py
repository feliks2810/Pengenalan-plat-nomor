"""
Kode Backend untuk Sistem PINHEL (Versi Terpadu)
----------------------------------------------
Menggunakan single YOLOv8 model (best.pt) untuk:
1. Deteksi pelanggaran helm (no-helmet)
2. Deteksi plat nomor (license-plate)
"""

import os
import cv2
import numpy as np
import time
import json
import uuid
import datetime
import threading
from flask import Flask, Response, jsonify, request, send_from_directory, render_template
from flask_cors import CORS
import base64
from ultralytics import YOLO
from collections import deque
import easyocr
from datetime import datetime as dt

# Konfigurasi
MODEL_PATH = 'best.pt'
CONFIDENCE_THRESHOLD = 0.35
MAX_QUEUE_SIZE = 5
FRAME_WIDTH = 640
FRAME_HEIGHT = 480
FPS_TARGET = 5  # Turunkan FPS untuk stabilitas

# Direktori penyimpanan
DATA_DIR = "data"
IMAGE_DIR = os.path.join(DATA_DIR, "images")
os.makedirs(IMAGE_DIR, exist_ok=True)

# Inisialisasi Flask
app = Flask(__name__, static_folder='static')
CORS(app)

# Variabel global
latest_detection = {
    "timestamp": time.time(),
    "detections": None
}
violations = []
frame_buffer = deque(maxlen=MAX_QUEUE_SIZE)
frame_ready = threading.Event()
processing_lock = threading.Lock()
latest_frame = None

# Inisialisasi model dan OCR
try:
    abs_model_path = os.path.abspath(MODEL_PATH)
    print(f"Mencoba memuat model dari: {abs_model_path}")
    if not os.path.exists(abs_model_path):
        print(f"PERINGATAN: File model tidak ditemukan di path {abs_model_path}")
        possible_paths = [MODEL_PATH, f"./{MODEL_PATH}", f"../{MODEL_PATH}", f"models/{MODEL_PATH}", f"./models/{MODEL_PATH}"]
        for path in possible_paths:
            if os.path.exists(path):
                print(f"Model ditemukan di lokasi alternatif: {path}")
                MODEL_PATH = path
                break
    
    detection_model = YOLO(MODEL_PATH)
    class_names = detection_model.names
    print(f"Model berhasil dimuat dari: {MODEL_PATH}")
    print(f"Kelas yang tersedia: {class_names}")
    test_img = np.zeros((FRAME_HEIGHT, FRAME_WIDTH, 3), dtype=np.uint8)
    test_results = detection_model(test_img, verbose=False)
    print(f"Tes deteksi berhasil. Mode model: {detection_model.task}")
except Exception as e:
    print(f"Gagal memuat model: {str(e)}")
    detection_model = None

# Inisialisasi OCR
reader = easyocr.Reader(['id', 'en'], gpu=False)  # Tambahkan 'id' untuk plat nomor Indonesia

# Inisialisasi kamera
camera = None
try:
    camera = cv2.VideoCapture(0)  # Coba indeks lain jika gagal (1, 2, dll.)
    if not camera.isOpened():
        print("PERINGATAN: Kamera tidak dapat dibuka dengan indeks 0. Mencoba indeks lain...")
        for i in range(1, 3):  # Coba indeks 1 dan 2
            camera = cv2.VideoCapture(i)
            if camera.isOpened():
                print(f"Kamera berhasil dibuka dengan indeks {i}")
                break
    if camera.isOpened():
        camera.set(cv2.CAP_PROP_FRAME_WIDTH, FRAME_WIDTH)
        camera.set(cv2.CAP_PROP_FRAME_HEIGHT, FRAME_HEIGHT)
        camera.set(cv2.CAP_PROP_FPS, FPS_TARGET)
        print("Kamera berhasil dikonfigurasi")
    else:
        print("ERROR: Tidak dapat mengakses kamera. Menggunakan mode simulasi.")
except Exception as e:
    print(f"ERROR: Tidak dapat mengakses kamera: {str(e)}. Menggunakan mode simulasi.")
    camera = None

def detect_objects(frame):
    if detection_model is None:
        no_helmet = np.random.random() > 0.6
        helmet_conf = np.random.random() * 0.3 + 0.7 if no_helmet else 0
        plate_number = ""
        plate_conf = np.random.random() * 0.3 + 0.6 if no_helmet else 0
        return no_helmet, helmet_conf, plate_number, plate_conf, frame.copy()
    
    annotated_frame = frame.copy()
    no_helmet_detected = False
    helmet_confidence = 0
    plate_detected = False
    plate_confidence = 0
    plate_number = ""  # Inisialisasi plate_number
    
    try:
        results = detection_model(frame, conf=0.25, verbose=False)
        class_names = detection_model.names
        
        for result in results:
            boxes = result.boxes
            for box in boxes:
                class_id = int(box.cls[0])
                confidence = float(box.conf[0])
                xyxy = box.xyxy[0].cpu().numpy()
                class_name = class_names.get(class_id, f"class_{class_id}")
                print(f"Deteksi: {class_name} (ID: {class_id}) dengan confidence {confidence:.2f}")
                
                # Sesuaikan dengan ID atau nama kelas yang benar
                is_no_helmet = class_id == 29 or "no helm" in class_name.lower() or "no-helm" in class_name.lower()
                is_license_plate = class_id == 1 or "plate" in class_name.lower() or "plat" in class_name.lower()
                
                if is_no_helmet:
                    if confidence > CONFIDENCE_THRESHOLD:
                        no_helmet_detected = True
                        if confidence > helmet_confidence:
                            helmet_confidence = confidence
                        cv2.rectangle(annotated_frame, (int(xyxy[0]), int(xyxy[1])), (int(xyxy[2]), int(xyxy[3])), (255, 0, 255), 2)
                        cv2.putText(annotated_frame, f"No Helm {confidence:.2f}", (int(xyxy[0]), int(xyxy[1])-10), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 0, 255), 2)
                
                elif is_license_plate:
                    if confidence > CONFIDENCE_THRESHOLD:
                        plate_detected = True
                        if confidence > plate_confidence:
                            plate_confidence = confidence
                        # Potong wilayah plat nomor dengan padding lebih besar
                        x1, y1, x2, y2 = int(xyxy[0]), int(xyxy[1]), int(xyxy[2]), int(xyxy[3])
                        padding = 10  # Tingkatkan padding untuk memastikan teks lengkap
                        plate_roi = frame[max(0, y1-padding):min(FRAME_HEIGHT, y2+padding), max(0, x1-padding):min(FRAME_WIDTH, x2+padding)]
                        if plate_roi.size > 0:
                            # Preprocessing yang ditingkatkan
                            plate_gray = cv2.cvtColor(plate_roi, cv2.COLOR_BGR2GRAY)
                            # Tingkatkan kontras
                            plate_gray = cv2.equalizeHist(plate_gray)
                            # Thresholding dengan nilai tetap
                            _, plate_binary = cv2.threshold(plate_gray, 150, 255, cv2.THRESH_BINARY_INV)
                            # Morfologi untuk menghapus noise
                            kernel = np.ones((3, 3), np.uint8)
                            plate_binary = cv2.erode(plate_binary, kernel, iterations=1)
                            plate_binary = cv2.dilate(plate_binary, kernel, iterations=1)
                            # Resize untuk meningkatkan resolusi
                            plate_binary = cv2.resize(plate_binary, None, fx=2, fy=2, interpolation=cv2.INTER_CUBIC)
                            # OCR dengan parameter tambahan
                            result = reader.readtext(plate_binary, allowlist='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ', paragraph=False, detail=0)
                            plate_number = result[0] if result and len(result) > 0 else "Tidak Terbaca"
                            print(f"Plat nomor terdeteksi: {plate_number}")
                        cv2.rectangle(annotated_frame, (x1, y1), (x2, y2), (0, 255, 255), 2)
                        cv2.putText(annotated_frame, f"Plat: {plate_number} ({plate_confidence:.2f})", (x1, y1-10), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 255, 255), 2)
                
                else:
                    cv2.rectangle(annotated_frame, (int(xyxy[0]), int(xyxy[1])), (int(xyxy[2]), int(xyxy[3])), (255, 0, 0), 1)
                    cv2.putText(annotated_frame, f"{class_name} {confidence:.2f}", (int(xyxy[0]), int(xyxy[1])-10), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (255, 0, 0), 1)
    
    except Exception as e:
        print(f"Error saat deteksi objek: {str(e)}")
        cv2.putText(annotated_frame, f"Error: {str(e)}", (10, 60), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 0, 255), 1)
    
    return no_helmet_detected, helmet_confidence, plate_number, plate_confidence, annotated_frame

def process_frames():
    global latest_frame
    while True:
        if camera is None:
            dummy_frame = np.zeros((FRAME_HEIGHT, FRAME_WIDTH, 3), dtype=np.uint8)
            cv2.putText(dummy_frame, "SIMULASI MODE", (50, 50), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)
            frame = dummy_frame
        else:
            success, frame = camera.read()
            if not success:
                print("Gagal membaca frame. Mencoba ulang...")
                time.sleep(1)
                continue
            print("Frame berhasil dibaca")

        start_time = time.time()
        no_helmet, helmet_conf, plate_number, plate_conf, output_frame = detect_objects(frame)
        print(f"Deteksi: no_helm={no_helmet}, helmet_conf={helmet_conf:.2f}, plate_number={plate_number}, plate_conf={plate_conf:.2f}")
        
        latest_detection = {
            "timestamp": time.time(),
            "detections": {
                "no_helm": helmet_conf if no_helmet else 0,
                "license_plate": {
                    "text": plate_number,
                    "confidence": plate_conf
                } if plate_number and plate_conf > 0 else None
            }
        }
        
        fps = 1.0 / (time.time() - start_time)
        cv2.putText(output_frame, f"FPS: {fps:.1f}", (10, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)
        
        with processing_lock:
            latest_frame = output_frame.copy()
            if len(frame_buffer) < MAX_QUEUE_SIZE:
                frame_buffer.append(output_frame)
                frame_ready.set()
        
        # Simpan pelanggaran hanya jika plat nomor terbaca
        if no_helmet and helmet_conf > CONFIDENCE_THRESHOLD and plate_number != "Tidak Terbaca":
            violation_id = str(uuid.uuid4())
            image_filename = f"{violation_id}.jpg"
            image_path = os.path.join(IMAGE_DIR, image_filename)
            cv2.imwrite(image_path, output_frame)
            timestamp = datetime.datetime.now().isoformat()
            violation = {
                "id": violation_id,
                "timestamp": timestamp,
                "plateNumber": plate_number,
                "plateConfidence": float(plate_conf),
                "violationType": "Tidak menggunakan helm",
                "helmConfidence": float(helmet_conf),
                "imageFile": image_filename,
                "created_at": timestamp
            }
            violations.append(violation)
            print(f"Pelanggaran disimpan: {violation}")

        time.sleep(1.0 / FPS_TARGET)

def generate_frames():
    while True:
        frame_ready.wait()
        with processing_lock:
            if frame_buffer:
                frame = frame_buffer.popleft()
                if len(frame_buffer) == 0:
                    frame_ready.clear()
            else:
                continue
        
        ret, buffer = cv2.imencode('.jpg', frame, [cv2.IMWRITE_JPEG_QUALITY, 80])
        frame_bytes = buffer.tobytes()
        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame_bytes + b'\r\n')
        time.sleep(0.01)

# Buat folder untuk template jika belum ada
os.makedirs('templates', exist_ok=True)

template_path = os.path.join('templates', 'index.html')
if not os.path.exists(template_path):
    with open(template_path, 'w') as f:
        f.write("""
        <!DOCTYPE html>
        <html>
        <head>
            <title>PINHEL - Sistem Deteksi Pelanggaran Helm</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #333; }
                .container { display: flex; flex-direction: column; align-items: center; }
                .video-container { margin-bottom: 20px; }
                .violations { width: 80%; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background-color: #f2f2f2; }
                .refresh { margin-bottom: 10px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>PINHEL - Sistem Deteksi Pelanggaran Helm</h1>
                <div class="video-container">
                    <h2>Live Feed</h2>
                    <img src="/video_feed" width="640" height="480" />
                </div>
                <div class="violations">
                    <h2>Pelanggaran Terbaru</h2>
                    <button class="refresh" onclick="getViolations()">Refresh</button>
                    <table id="violations-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Plat Nomor</th>
                                <th>Jenis Pelanggaran</th>
                                <th>Gambar</th>
                            </tr>
                        </thead>
                        <tbody id="violations-body">
                            <!-- Data akan diisi secara dinamis -->
                        </tbody>
                    </table>
                </div>
            </div>
            <script>
                function getViolations() {
                    fetch('/api/violations')
                        .then(response => response.json())
                        .then(data => {
                            const tableBody = document.getElementById('violations-body');
                            tableBody.innerHTML = '';
                            data.violations.forEach(violation => {
                                const row = document.createElement('tr');
                                const timeCell = document.createElement('td');
                                const date = new Date(violation.timestamp);
                                timeCell.textContent = date.toLocaleString();
                                row.appendChild(timeCell);
                                const plateCell = document.createElement('td');
                                plateCell.textContent = violation.plateNumber || 'Belum Teridentifikasi';
                                row.appendChild(plateCell);
                                const violationCell = document.createElement('td');
                                violationCell.textContent = violation.violationType;
                                row.appendChild(violationCell);
                                const imageCell = document.createElement('td');
                                if (violation.imageFile) {
                                    const img = document.createElement('img');
                                    img.src = `/api/images/${violation.imageFile}`;
                                    img.width = 100;
                                    imageCell.appendChild(img);
                                } else {
                                    imageCell.textContent = 'Tidak ada gambar';
                                }
                                row.appendChild(imageCell);
                                tableBody.appendChild(row);
                            });
                        })
                        .catch(error => console.error('Error:', error));
                }
                document.addEventListener('DOMContentLoaded', getViolations);
                setInterval(getViolations, 10000);
            </script>
        </body>
        </html>
        """)

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/video_feed')
def video_feed():
    return Response(generate_frames(),
                   mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/api/latest_detection')
def get_latest_detection():
    return jsonify(latest_detection)

@app.route('/api/capture_violation', methods=['POST'])
def capture_violation():
    try:
        with processing_lock:
            if latest_frame is None:
                return jsonify({"status": "error", "message": "Tidak ada frame tersedia"}), 400
            frame = latest_frame.copy()
        
        no_helmet, helmet_conf, plate_number, plate_conf, _ = detect_objects(frame)
        if not no_helmet or helmet_conf < CONFIDENCE_THRESHOLD or plate_number == "Tidak Terbaca":
            return jsonify({"status": "error", "message": "Tidak ada pelanggaran terdeteksi atau plat tidak terbaca"}), 400
        
        violation_id = str(uuid.uuid4())
        image_filename = f"{violation_id}.jpg"
        image_path = os.path.join(IMAGE_DIR, image_filename)
        cv2.imwrite(image_path, frame)
        
        timestamp = datetime.datetime.now().isoformat()
        violation = {
    "timestamp": timestamp,
    "plateNumber": plate_number,
    "plateConfidence": float(plate_conf),
    "violationType": "Tidak menggunakan helm",
    "helmConfidence": float(helmet_conf),
    "imageFile": image_filename,
    "created_at": timestamp
}
        violations.append(violation)
        return jsonify({"status": "success", "id": violation_id, "violation": violation})
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

@app.route('/api/violations', methods=['POST'])
def save_violation():
    try:
        data = request.json
        violation_id = str(uuid.uuid4())
        timestamp = data.get('timestamp', datetime.datetime.now().isoformat())
        plate_number = data.get('plateNumber', '')
        violation_type = data.get('violationType', 'Tidak menggunakan helm')
        plate_confidence = data.get('plateConfidence', 0)
        helm_confidence = data.get('helmConfidence', 0)
        
        image_filename = None
        if 'imageData' in data:
            img_data = data['imageData']
            if ',' in img_data:
                img_data = img_data.split(',')[1]
            image_filename = f"{violation_id}.jpg"
            image_path = os.path.join(IMAGE_DIR, image_filename)
            with open(image_path, "wb") as img_file:
                img_file.write(base64.b64decode(img_data))
        
        if plate_number == "Tidak Terbaca":
            return jsonify({"status": "error", "message": "Plat nomor tidak terbaca, pelanggaran tidak disimpan"}), 400
        
        violation = {
            "id": violation_id,
            "timestamp": timestamp,
            "plateNumber": plate_number,
            "plateConfidence": plate_confidence,
            "violationType": violation_type,
            "helmConfidence": helm_confidence,
            "imageFile": image_filename,
            "created_at": datetime.datetime.now().isoformat()
        }
        violations.append(violation)
        return jsonify({"status": "success", "id": violation_id})
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500

@app.route('/api/violations', methods=['GET'])
def get_violations():
    return jsonify({"violations": violations})

@app.route('/api/stats', methods=['GET'])
def get_stats():
    time_range = request.args.get('time_range', 'harian')
    current_time = dt.now()
    
    filtered_violations = []
    for violation in violations:
        violation_time = dt.fromisoformat(violation['timestamp'].replace('Z', '+00:00'))
        if time_range == 'harian' and violation_time.date() == current_time.date():
            filtered_violations.append(violation)
        elif time_range == 'mingguan' and (current_time - violation_time).days <= 7:
            filtered_violations.append(violation)
        elif time_range == 'bulanan' and (current_time - violation_time).days <= 30:
            filtered_violations.append(violation)
    
    stats = {}
    for violation in filtered_violations:
        date = violation_time.date().isoformat()
        if date not in stats:
            stats[date] = {'count': 0, 'unique_plates': set()}
        stats[date]['count'] += 1
        stats[date]['unique_plates'].add(violation['plateNumber'])
    
    result = [{'date': date, 'count': data['count'], 'unique_plates': list(data['unique_plates'])} for date, data in stats.items()]
    return jsonify({"stats": result})

@app.route('/api/images/<filename>')
def get_violation_image(filename):
    return send_from_directory(IMAGE_DIR, filename)

if __name__ == '__main__':
    print("Memulai server PINHEL...")
    print(f"Menggunakan model: {MODEL_PATH}")
    print(f"Confidence threshold: {CONFIDENCE_THRESHOLD}")
    
    if detection_model is not None:
        class_names = detection_model.names
        print(f"Kelas terdeteksi dalam model: {class_names}")
        print(f"Tugas model: {detection_model.task}")
        if 0 not in class_names:
            print("PERINGATAN: Kelas ID 0 (no-helmet) tidak ditemukan dalam model!")
        if 1 not in class_names:
            print("PERINGATAN: Kelas ID 1 (license-plate) tidak ditemukan dalam model!")
    
    frame_thread = threading.Thread(target=process_frames, daemon=True)
    frame_thread.start()
    app.run(host='0.0.0.0', port=5000, threaded=True, debug=False)