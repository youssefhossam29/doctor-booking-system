<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\DoctorResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            //'doctor'    => new DoctorResource($this->doctor),
            //'patient'   => new PatientResource($this->patient),
            'doctor'    => [
                'id' => $this->doctor_id,
                'name' => $this->doctor->user->name,
                'image' => $this->doctor->image,
                'specialization' => $this->doctor->specialization->name,
            ],
            'patient'    => [
                'id' => $this->patient_id,
                'name' => $this->patient->user->name,
                'image' => $this->patient->image,
            ],
            'date'      => $this->appointment_date,
            'time'      => $this->appointment_time,
            'status'    => $this->status,
            'notes'     => $this->notes,
        ];
    }
}
