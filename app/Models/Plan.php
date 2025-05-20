<?php

namespace App\Models;

use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, ModelTraits, SoftDeletes;

    protected $connection = 'fleet_db';
    const PAYSTACK = 'Paystack';

    //This specify the table being used
    protected $table = 'plans';

    public function paymentPlans()
    {
        return $this->hasMany(PaymentPlan::class, 'plan_id');
    }
}
