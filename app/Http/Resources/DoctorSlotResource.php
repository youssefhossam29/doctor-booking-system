<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorSlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'start_time'  => $this->start_time,
            'end_time'    => $this->end_time,
            'is_available'=> (bool) $this->is_available,
        ];
    }
}
