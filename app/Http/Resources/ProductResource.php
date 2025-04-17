<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'type' => $this->type,
            'type_capacity' => $this->type_capacity,
            'serial_number' => $this->serial_number,
            'status' => $this->status,
            'location' => $this->location,
            'assigned_to' => $this->assigned_to,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'assigned_technician' => new UserResource($this->whenLoaded('assignedTechnician')),
            'maintenance_records_count' => $this->whenCounted('maintenanceRecords'),
        ];
    }
}
