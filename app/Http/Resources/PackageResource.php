<?php

namespace App\Http\Resources;

use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
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
            'title' => $this->name,
            'billing_cycle' => $this->interval,
            'price' => $this->amount,
            'discount' => $this->discount,
            'features' => $this->formatPermissions($this->name),
            'description' => $this->description,
            'date_created' => $this->created_at
        ];
    }

    private function formatPermissions($name)
    {
        try {
            $role = Role::findByName($name, 'web');
            $permissions = $role->permissions;

            return $permissions->map(function ($permission) {
                $formattedName = str_replace('-', ' ', $permission->name);
                $formattedName = strtolower($formattedName);
                $formattedName = ucfirst($formattedName);
                $permission->name = $formattedName;

                return $permission->name;
            });
        } catch (Exception $e) {
            return [];
        }
    }
}
