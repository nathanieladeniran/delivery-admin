<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class FleetUser extends Model
{
    use HasFactory, Notifiable, HasApiTokens, ModelTraits, SoftDeletes;

    //connection to dora fleet database table
    protected $connection = 'fleet_db';

    //This specify the table being used
    protected $table = 'users';

     public function profile()
    {   
        // user_id is aforeing key in profile table
        return $this->hasOne(Profile::class, 'id', 'profile_id');
    }
}
