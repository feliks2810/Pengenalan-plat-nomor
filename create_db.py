# file: create_db.py
import sqlite3

def create_db():
    conn = sqlite3.connect('pelanggaran.db')
    c = conn.cursor()

    # Buat tabel pelanggaran jika belum ada
    c.execute('''
    CREATE TABLE IF NOT EXISTS pelanggaran (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        plat_nomor TEXT,
        waktu TEXT,
        gambar TEXT
    )
    ''')

    conn.commit()
    conn.close()

# Jalankan untuk membuat database
create_db()
