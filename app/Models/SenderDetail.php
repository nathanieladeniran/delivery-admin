<?php

namespace App\Models;

use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SenderDetail extends Model
{
    use HasFactory, ModelTraits;

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'sender_detail_id', 'id');
    }
}
