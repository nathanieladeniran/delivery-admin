<?php

namespace App\Models;

use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Model
{
    use HasFactory, ModelTraits;

    protected $connection = 'fleet_db';

    const TYPE_SMS_TOPUP = 'sms_topup';
    const TYPE_BOOKING = 'booking';
    const TYPE_SUBSCRIPTION = 'subscription';
    const SUCCESSFUL = 'successful';
    const PENDING = 'pending';

    public function transactionable()
    {
        return $this->morphTo();
    }

    public function scopeOfType(Builder $query, $type)
    {
        return $query->where('transactionable_type', $type);
    }

    public function fleetProfile()
    {
        return $this->belongsTo(FleetProfile::class, 'profile_id');
    }

    public function profile() {
        return $this->belongsTo(Profile::class);
    }
}
