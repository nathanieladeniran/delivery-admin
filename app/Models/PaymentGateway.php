<?php

namespace App\Models;

use App\Traits\BasemodelTrait;
use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory, SoftDeletes, ModelTraits;

    protected $connection = 'fleet_db';
}
