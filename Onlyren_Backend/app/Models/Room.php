<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'ruangan';         // Nama tabel di DB
    protected $primaryKey = 'id_ruangan'; // Primary key custom

    public $timestamps = false; // Jika tidak pakai created_at dan updated_at

    protected $fillable = [
        'nama_ruangan',
        'kapasitas',
        'fasilitas',
        'status',
        'harga',
    ];
}
