<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Violation extends Model
{
    protected $fillable = ['description', 'plateNumber', 'plateConfidence', 'violationType', 'helmConfidence', 'imageFile', 'image_path', 'timestamp'];
    protected $casts = [
        'timestamp' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}