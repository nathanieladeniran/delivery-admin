<?php

namespace App\Models;

use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class RiderProfile extends Model
{
    use HasFactory, Notifiable, HasApiTokens, ModelTraits, SoftDeletes;

    //connection to dora fleet database table
    protected $connection = 'rider_db';

    //This specify the table being used
    protected $table = 'profiles';
}
