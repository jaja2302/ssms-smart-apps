<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';

    protected $table = 'wil';

    public function regional()
    {
        return $this->belongsTo(Regional::class);
    }

    public function estate()
    {
        return $this->hasMany(Estate::class, 'wil');
    }
}
