<?php

namespace App\Models;

use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemDetail extends Model
{
    use HasFactory, ModelTraits;

    protected $connection = 'fleet_db';
    
    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }
}
