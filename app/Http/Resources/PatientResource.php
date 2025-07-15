<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'user'  => new UserResource($this->user),
            'image' => 'uploads/users/' . $this->image,
            'date_of_birth' => optional($this->date_of_birth)->toDateTimeString(),
            'gender' => $this->gender_type,
            'phone' => $this->phone,
        ];
    }
}
