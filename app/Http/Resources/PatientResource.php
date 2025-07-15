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
            'date_of_birth' => $this->date_of_birth
                ? \Carbon\Carbon::parse($this->date_of_birth)->format('Y-m-d')
                : null,
            'gender' => $this->gender_type,
            'phone' => $this->phone,
        ];
    }
}
