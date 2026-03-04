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
            'is_editable' => $this->is_editable,
            'is_deletable' => $this->is_deletable,
            'allows_children' => $this->allows_children,
            'parent_id' => $this->parent_id,
        ];
    }
}
