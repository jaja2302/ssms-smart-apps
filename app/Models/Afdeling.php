<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Afdeling extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $table = 'afdeling';

    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    public function blok()
    {
        return $this->hasMany(Blok::class, 'afdeling');
    }
}
