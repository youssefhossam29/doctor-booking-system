<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\SpecializationResource;

class DoctorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'user'            => new UserResource($this->user),
            'image'           => 'uploads/users/' . $this->image,
            'bio'             => $this->bio,
            'phone'           => $this->phone,
            'specialization'  =>  new SpecializationResource($this->specialization),
        ];
    }
}
