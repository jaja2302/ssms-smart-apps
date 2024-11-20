<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blok extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';

    protected $table = 'blok';

    public function afdeling()
    {
        return $this->belongsTo(Afdeling::class);
    }
}
