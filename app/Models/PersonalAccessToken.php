<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use App\Traits\ModelTraits;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use HasFactory, ModelTraits;    
}
