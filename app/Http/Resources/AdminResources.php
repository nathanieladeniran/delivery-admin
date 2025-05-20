<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->id,
            'profile_type' => $this->profile_type,
            'profile_id' => $this->profile_id,
            'email' => $this->email,
            'token' => $this->token,
            "email_verified_at"=> $this->email_verified_at,
            "profile" => new ProfileResources($this->profile),
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
        ];
    }
}
