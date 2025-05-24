<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggaran extends Model
{
    use HasFactory;
    
    // Tentukan nama tabel jika berbeda dari konvensi Laravel
    protected $table = 'pelanggarans';
    
    // Kolom yang bisa diisi (mass assignable)
    protected $fillable = [
        'nama_pelanggaran',
        'deskripsi',
        'poin_pelanggaran',
        // tambahkan kolom lain sesuai kebutuhan
    ];
    
    // Atur timestamps jika tidak menggunakan created_at dan updated_at
    // public $timestamps = false;
    
    // Tambahkan relasi jika diperlukan
    // contoh: satu pelanggaran dimiliki oleh banyak siswa
    // public function siswa()
    // {
    //     return $this->hasMany(Siswa::class);
    // }
}