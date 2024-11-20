<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Wilayah;

class Regional extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';

    protected $table = 'reg';

    public function wilayah()
    {
        return $this->hasMany(Wilayah::class, 'regional');
    }
}
