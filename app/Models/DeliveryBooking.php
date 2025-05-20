<?php

namespace App\Models;

use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryBooking extends Model
{
    use HasFactory, ModelTraits;

    protected $connection = 'fleet_db';

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'transactionable_id', 'id')
            ->where('transactionable_type', 'booking');
    }
}
