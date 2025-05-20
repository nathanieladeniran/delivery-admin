<?php

namespace App\Models;

use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliverySmsMessage extends Model
{
    use HasFactory, ModelTraits;

    protected $connection = 'fleet_db';
    const DELIVERED_STATUS = 'DELIVERED';
    const PENDING_STATUS = 'PENDING';
    const FAILED_STATUS = 'FAILED';

    public function smsable()
    {
        return $this->morphTo();
    }
}
