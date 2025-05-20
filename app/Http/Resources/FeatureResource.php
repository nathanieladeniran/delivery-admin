<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeatureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->formatName($this->name),
            'description' => $this->description,
            'date' => $this->created_at
        ];
    }

    private function formatName($name)
    {
        $formattedName = str_replace('-', ' ', $name);
        $formattedName = strtolower($formattedName);
        $formattedName = ucfirst($formattedName);

        return $formattedName;
    }
}
