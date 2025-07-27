<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorSlotResource;
use App\Models\Doctor;
use App\Models\DoctorSlot;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SlotController extends Controller
{
    //
     public function indexByDate(Request $request)
    {
        $validated = $request->validate([
            'date'      => ['required', 'date', 'date_format:Y-m-d'],
        ]);

        $doctor = request()->user()->doctor;
        $doctor->load(['user', 'schedules']);

        $this->authorize('viewAny', DoctorSlot::class);

        $slots = DoctorSlot::where('doctor_id', $doctor->id)
            ->where('date', $validated['date'])
            ->orderBy('start_time')
            ->get();

        $response = [
            'doctor' => [
                'id'              => $doctor->id,
                'name'            => $doctor->user->name,
                'image'           => $doctor->image,
                'specialization'  => optional($doctor->specialization)->name,
            ],
            'date'        => $validated['date'],
            'day_of_week' => Carbon::parse($validated['date'])->format('l'),
            'slots'       => DoctorSlotResource::collection($slots),
        ];

        return apiResponse($response, "Slots fetched successfully for {$validated['date']}", 200);
    }


    public function destroyByDate(Request $request)
    {
        $validated = $request->validate([
            'date'      => ['required', 'date', 'date_format:Y-m-d'],
        ]);

        $doctor = request()->user()->doctor;

        $this->authorize('deleteByDate', DoctorSlot::class);

        $count = DoctorSlot::where('doctor_id', $doctor->id)
            ->where('date', $validated['date'])
            ->delete();

        return apiResponse([], "$count slots deleted for {$validated['date']}");
    }


    public function destroy(DoctorSlot $doctorSlot)
    {
        $this->authorize('delete', $doctorSlot);

        $slot = $doctorSlot;
        $doctorSlot->delete();

        return apiResponse([], "Slot at {$slot->start_time} on {$slot->date} deleted");
    }
}
