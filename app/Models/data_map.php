<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class data_map extends Model
{
    use HasFactory;


    protected $primaryKey = 'id'; // Assuming 'id' is the primary key field.
    protected $connection = 'mysql2';

    protected $table = 'afdeling_plot';
    protected $fillable = [
        'est',
        'afd',
        'lat',
        'lon',
    ];

    public $timestamps = false; // This line tells Eloquent to manage the timestamps.


}
