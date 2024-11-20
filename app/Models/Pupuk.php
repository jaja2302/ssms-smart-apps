<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pupuk extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';

    protected $table = 'pupuk';

    protected $fillable = ['nama'];
    public $timestamps = false;
}
