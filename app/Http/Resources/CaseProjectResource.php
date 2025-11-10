<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseProjectResource extends JsonResource
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
            'description' => $this->description,
            'case_date' => $this->case_date,
            'status' => $this->status,
            'link_dokumen' => $this->link_dokumen,
            'staff' => [
                'id' => $this->staff?->id,
                'name' => $this->staff?->name,
                'phone' => $this->staff?->phone,
                'position' => $this->staff?->positionReference?->name,
                'department' => $this->staff?->departmentReference?->name,
            ],
            'client' => [
                'id' => $this->client?->id,
                'company_name' => $this->client?->company_name,
                'contact_person' => $this->client?->contact_person,
                'phone' => $this->client?->phone,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
