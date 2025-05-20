<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Delivery extends Model
{
    use HasFactory, Notifiable, HasApiTokens, ModelTraits, SoftDeletes;

    //connection to dora fleet database table
    const DELIVERED = 3;
    protected $connection = 'fleet_db';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(ReceiverDetail::class, 'customer_detail_id', 'id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(SenderDetail::class, 'sender_detail_id', 'id');
    }

    public function itemDetails()
    {
        return $this->hasMany(ItemDetail::class, 'delivery_id');
    }
}
