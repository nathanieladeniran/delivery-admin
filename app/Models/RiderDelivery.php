<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderDelivery extends Model
{
    use HasFactory;

    //connection to dora rider database table
    protected $connection = 'rider_db';

    //This specify the table being used
    protected $table = 'deliveries';
}
