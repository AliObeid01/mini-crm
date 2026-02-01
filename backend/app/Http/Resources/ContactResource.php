<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone_number' => $this->phone_number,
            'birthdate' => $this->birthdate?->format('Y-m-d'),
            'city' => $this->city,
            'departments_count' => $this->whenLoaded('departments', fn () => $this->departments->count()),
            'departments' => $this->whenLoaded('departments', function () {
                    return $this->departments->map(fn ($d) => [
                        'id' => $d->id,
                        'name' => $d->name,
                    ]);
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
