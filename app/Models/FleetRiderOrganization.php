<?php

namespace App\Models;

use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetRiderOrganization extends Model
{
    use HasFactory, ModelTraits;

     //connection to dora rider database table
     protected $connection = 'fleet_db';

     //This specify the table being used
     protected $table = 'rider_organizations';
}
