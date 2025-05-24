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

# Konfigurasi
MODEL_PATH = 'best.pt'  # Model terpadu untuk deteksi no-helm dan plat nomor
CONFIDENCE_THRESHOLD = 0.35  # Ambang batas kepercayaan deteksi (diturunkan untuk meningkatkan sensitivitas)
MAX_QUEUE_SIZE = 5  # Ukuran maksimum antrian frame
FRAME_WIDTH = 640
FRAME_HEIGHT = 480
FPS_TARGET = 10  # Target FPS

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

# Inisialisasi model
try:
    # Model harus berada di direktori saat ini
    abs_model_path = os.path.abspath(MODEL_PATH)
    print(f"Mencoba memuat model dari: {abs_model_path}")
    if not os.path.exists(abs_model_path):
        print(f"PERINGATAN: File model tidak ditemukan di path {abs_model_path}")
        # Coba cari model di direktori tertentu
        possible_paths = [
            MODEL_PATH,
            f"./{MODEL_PATH}",
            f"../{MODEL_PATH}",
            f"models/{MODEL_PATH}",
            f"./models/{MODEL_PATH}"
        ]
        for path in possible_paths:
            if os.path.exists(path):
                print(f"Model ditemukan di lokasi alternatif: {path}")
                MODEL_PATH = path
                break
    
    # Memuat model YOLOv8
    detection_model = YOLO(MODEL_PATH)
    
    # Tampilkan informasi kelas yang ada di model
    class_names = detection_model.names
    print(f"Model berhasil dimuat dari: {MODEL_PATH}")
    print(f"Kelas yang tersedia dalam model: {class_names}")
    
    # Pastikan model bisa mendeteksi objek sederhana (test)
    print("Melakukan tes deteksi sederhana...")
    test_img = np.zeros((FRAME_HEIGHT, FRAME_WIDTH, 3), dtype=np.uint8)
    test_results = detection_model(test_img, verbose=False)
    print(f"Tes deteksi berhasil. Mode model: {detection_model.task}")
    
except Exception as e:
    print(f"Gagal memuat model: {str(e)}")
    detection_model = None

# Inisialisasi kamera
camera = None
try:
    camera = cv2.VideoCapture(0)
    camera.set(cv2.CAP_PROP_FRAME_WIDTH, FRAME_WIDTH)
    camera.set(cv2.CAP_PROP_FRAME_HEIGHT, FRAME_HEIGHT)
    camera.set(cv2.CAP_PROP_FPS, FPS_TARGET)
    
    if not camera.isOpened():
        print("PERINGATAN: Kamera tidak dapat dibuka. Menggunakan mode simulasi.")
        camera = None
except Exception as e:
    print(f"ERROR: Tidak dapat mengakses kamera: {str(e)}. Menggunakan mode simulasi.")
    camera = None

def detect_objects(frame):
    """
    Mendeteksi no-helm dan plat nomor dalam satu frame
    Returns:
        - no_helmet_detected: boolean
        - helmet_confidence: float
        - plate_number: str (dummy, perlu OCR sebenarnya)
        - plate_confidence: float
        - annotated_frame: frame dengan bounding box
    """
    if detection_model is None:
        # Mode simulasi
        no_helmet = np.random.random() > 0.6
        helmet_conf = np.random.random() * 0.3 + 0.7 if no_helmet else 0
        plate_number = "" # Tidak ada plat nomor dalam mode simulasi
        plate_conf = np.random.random() * 0.3 + 0.6 if no_helmet else 0
        return no_helmet, helmet_conf, plate_number, plate_conf, frame.copy()
    
    # Salin frame untuk annotasi
    annotated_frame = frame.copy()
    no_helmet_detected = False
    helmet_confidence = 0
    plate_detected = False
    plate_confidence = 0
    plate_boxes = []
    
    # Deteksi objek
    try:
        # Gunakan model YOLOv8 dengan confidence threshold lebih rendah untuk meningkatkan kemungkinan deteksi
        results = detection_model(frame, conf=0.25, verbose=False)
        
        # Ambil nama-nama kelas dari model
        class_names = detection_model.names
        print(f"Kelas yang terdeteksi: {class_names}")
        
        for result in results:
            boxes = result.boxes
            for box in boxes:
                class_id = int(box.cls[0])
                confidence = float(box.conf[0])
                xyxy = box.xyxy[0].cpu().numpy()
                class_name = class_names.get(class_id, f"class_{class_id}")
                
                # Print untuk debugging
                print(f"Deteksi: {class_name} (ID: {class_id}) dengan confidence {confidence:.2f}")
                
                # Deteksi berdasarkan nama kelas juga, tidak hanya ID
                is_no_helmet = class_id == 0 or "helmet" in class_name.lower() or "helm" in class_name.lower()
                is_license_plate = class_id == 1 or "plate" in class_name.lower() or "plat" in class_name.lower()
                
                if is_no_helmet:  # no-helmet / pelanggaran helm
                    if confidence > CONFIDENCE_THRESHOLD:
                        no_helmet_detected = True
                        if confidence > helmet_confidence:
                            helmet_confidence = confidence
                        # Gambar bounding box ungu
                        cv2.rectangle(annotated_frame, 
                                    (int(xyxy[0]), int(xyxy[1])),
                                    (int(xyxy[2]), int(xyxy[3])),
                                    (255, 0, 255), 2)
                        cv2.putText(annotated_frame, f"No Helm {confidence:.2f}",
                                (int(xyxy[0]), int(xyxy[1])-10),
                                cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 0, 255), 2)
                
                elif is_license_plate:  # license-plate / plat nomor
                    if confidence > CONFIDENCE_THRESHOLD:
                        plate_detected = True
                        if confidence > plate_confidence:
                            plate_confidence = confidence
                        plate_boxes.append(xyxy)
                        # Gambar bounding box kuning
                        cv2.rectangle(annotated_frame,
                                    (int(xyxy[0]), int(xyxy[1])),
                                    (int(xyxy[2]), int(xyxy[3])),
                                    (0, 255, 255), 2)
                else:
                    # Untuk kelas lain, juga gambar bounding box (biru) untuk debugging
                    cv2.rectangle(annotated_frame,
                                (int(xyxy[0]), int(xyxy[1])),
                                (int(xyxy[2]), int(xyxy[3])),
                                (255, 0, 0), 1)
                    cv2.putText(annotated_frame, f"{class_name} {confidence:.2f}",
                            (int(xyxy[0]), int(xyxy[1])-10),
                            cv2.FONT_HERSHEY_SIMPLEX, 0.5, (255, 0, 0), 1)
    
    except Exception as e:
        print(f"Error saat deteksi objek: {str(e)}")
        cv2.putText(annotated_frame, f"Error: {str(e)}", (10, 60), 
                    cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 0, 255), 1)
    
    # Proses plat nomor (dummy, ganti dengan OCR sebenarnya)
    plate_number = ""
    if plate_detected:
        # Disini harusnya implementasi OCR untuk membaca plat nomor
        # Tapi karena belum ada OCR, kita biarkan kosong dulu
        for box in plate_boxes:
            # Tampilkan hanya confidence tanpa dummy plate number
            cv2.putText(annotated_frame, f"Plat: {plate_confidence:.2f}",
                       (int(box[0]), int(box[1])-10),
                       cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 255, 255), 2)
    
    return no_helmet_detected, helmet_confidence, plate_number, plate_confidence, annotated_frame

def process_frames():
    """Thread untuk memproses frame"""
    global latest_frame
    
    while True:
        if camera is None:
            # Mode simulasi
            dummy_frame = np.zeros((FRAME_HEIGHT, FRAME_WIDTH, 3), dtype=np.uint8)
            cv2.putText(dummy_frame, "SIMULASI MODE", (50, 50), 
                        cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)
            frame = dummy_frame
        else:
            success, frame = camera.read()
            if not success:
                time.sleep(0.1)
                continue
        
        # Proses deteksi
        start_time = time.time()
        no_helmet, helmet_conf, plate_number, plate_conf, output_frame = detect_objects(frame)
        
        # Update deteksi terbaru
        global latest_detection
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
        
        # Hitung FPS
        fps = 1.0 / (time.time() - start_time)
        cv2.putText(output_frame, f"FPS: {fps:.1f}", (10, 30), 
                    cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)
        
        # Simpan frame terbaru
        with processing_lock:
            latest_frame = output_frame.copy()
            
        # Tambahkan ke buffer
        if len(frame_buffer) < MAX_QUEUE_SIZE:
            frame_buffer.append(output_frame)
            frame_ready.set()
        
        time.sleep(1.0/FPS_TARGET)

def generate_frames():
    """Generator untuk streaming video"""
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

# Buat file template HTML sederhana jika belum ada
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
                // Fungsi untuk mendapatkan data pelanggaran terbaru
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
                
                // Panggil fungsi saat halaman dimuat
                document.addEventListener('DOMContentLoaded', getViolations);
                
                // Refresh setiap 10 detik
                setInterval(getViolations, 10000);
            </script>
        </body>
        </html>
        """)

# Endpoint API
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
        # Ambil frame terakhir
        with processing_lock:
            if latest_frame is None:
                return jsonify({"status": "error", "message": "Tidak ada frame tersedia"}), 400
            frame = latest_frame.copy()
        
        # Deteksi ulang pada frame ini untuk mendapatkan data terbaru
        no_helmet, helmet_conf, plate_number, plate_conf, _ = detect_objects(frame)
        
        if not no_helmet or helmet_conf < CONFIDENCE_THRESHOLD:
            return jsonify({"status": "error", "message": "Tidak ada pelanggaran terdeteksi"}), 400
        
        # Simpan gambar
        violation_id = str(uuid.uuid4())
        image_filename = f"{violation_id}.jpg"
        image_path = os.path.join(IMAGE_DIR, image_filename)
        cv2.imwrite(image_path, frame)
        
        # Buat data pelanggaran
        timestamp = datetime.datetime.now().isoformat()
        violation = {
            "id": violation_id,
            "timestamp": timestamp,
            "plateNumber": plate_number if plate_number else "",
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

@app.route('/api/images/<filename>')
def get_violation_image(filename):
    return send_from_directory(IMAGE_DIR, filename)

if __name__ == '__main__':
    print("Memulai server PINHEL...")
    print(f"Menggunakan model: {MODEL_PATH}")
    print(f"Confidence threshold: {CONFIDENCE_THRESHOLD}")
    
    # Mencari kelas di model YOLOv8
    if detection_model is not None:
        class_names = detection_model.names
        print(f"Kelas terdeteksi dalam model: {class_names}")
        print(f"Tugas model: {detection_model.task}")
        if 0 not in class_names:
            print("PERINGATAN: Kelas ID 0 (no-helmet) tidak ditemukan dalam model!")
        if 1 not in class_names:
            print("PERINGATAN: Kelas ID 1 (license-plate) tidak ditemukan dalam model!")
    
    # Mulai thread untuk pemrosesan frame
    frame_thread = threading.Thread(target=process_frames, daemon=True)
    frame_thread.start()
    
    # Jalankan server Flask
    app.run(host='0.0.0.0', port=5000, threaded=True, debug=False)