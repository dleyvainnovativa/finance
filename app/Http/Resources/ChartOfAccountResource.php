<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChartOfAccountResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'nature' => $this->nature,
            'category' => $this->category,
            'opening_balance' => $this->opening_balance,
            'is_active' => $this->is_active,
            'parent_id' => $this->parent_id,
        ];
    }
}
