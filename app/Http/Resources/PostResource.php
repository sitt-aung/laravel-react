<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'title' => $this->title,
            'category_id' => $this->category_id,
            'category' => [
                'id' => $this->category->id ?? '',
                'name' => $this->category->name ?? '',
            ],
            'content' => substr($this->content, 0, 50) . '...',
            'created_at' => $this->created_at->toDateTimeString()
        ];
    }
}
