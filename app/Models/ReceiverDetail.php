<?php

namespace App\Models;

use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiverDetail extends Model
{
    use HasFactory, ModelTraits;

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'receiver_detail_id', 'id');
    }
}
