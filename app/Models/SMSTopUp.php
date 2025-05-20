<?php

namespace App\Models;

use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMSTopUp extends Model
{
    use HasFactory, ModelTraits;
    
    protected $connection = 'fleet_db';

    public function transactionable()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }
}
