<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
        'project_id' => $this->project?->public_id,
        'title' => $this->title,
        'description' => $this->description,
        'status' => strtoupper($this->status), // We can transform data!
        'priority' => $this->priority,
        'assigned_to' => $this->developer?->public_id,
        'assigned_to_user' => $this->developer->name, // Using our relationship
        'deadline_info' => $this->project->deadline,
    ];
    }
}
