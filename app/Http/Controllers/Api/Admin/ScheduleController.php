<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\DoctorSlot;
use App\Models\Doctor;
use Carbon\Carbon;
use App\Http\Requests\Api\Admin\StoreScheduleRequest;
use App\Http\Requests\Api\Admin\UpdateScheduleRequest;
use App\Http\Resources\ScheduleResource;

class ScheduleController extends Controller
{
    public function index(Doctor $doctor)
    {
        $doctor->load(['user', 'schedules']);

        return apiResponse([
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->user->name,
                'image' => $doctor->image,
            ],
            'schedules' => ScheduleResource::collection($doctor->schedules),
        ], "Schedules for Dr. {$doctor->user->name} fetched successfully.");
    }

    public function store(StoreScheduleRequest $request)
    {
        $schedule = Schedule::create($request->validated());
        $this->generateSlotsForDay($schedule);

        return apiResponse([
            'schedule' => new ScheduleResource($schedule),
        ], "Schedule for {$schedule->day_of_week} created and slots generated successfully.", 201);
    }


    public function show(Schedule $schedule)
    {
        return apiResponse([
            'schedule' => new ScheduleResource($schedule),
        ], "Schedule for {$schedule->day_of_week} fetched successfully.");
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        $schedule->update($request->validated());

        $this->deleteSlotsForDay($schedule);
        $this->generateSlotsForDay($schedule);

        return apiResponse([
            'schedule' => new ScheduleResource($schedule),
        ], "Schedule for {$schedule->day_of_week} updated and slots regenerated successfully.");
    }

    public function destroy(Schedule $schedule)
    {
        $day_of_week = $schedule->day_of_week;
        $this->deleteSlotsForDay($schedule);
        $schedule->delete();

        return apiResponse([], "Schedule and slots for $day_of_week deleted successfully");
    }

    public function repeat(Doctor $doctor)
    {
        $doctor->load('schedules');

        foreach ($doctor->schedules as $schedule) {
            $dayName = strtolower($schedule->day_of_week);

            $lastSlot = DoctorSlot::where('doctor_id', $doctor->id)
                ->where('date', '>=', now()->startOfWeek())
                ->whereRaw('LOWER(DAYNAME(date)) = ?', [$dayName])
                ->orderByDesc('date')
                ->first();

            $nextDate = $lastSlot
                ? Carbon::parse($lastSlot->date)->addWeek()
                : now()->next(strtolower($schedule->day_of_week));

            $start = Carbon::parse("{$nextDate->format('Y-m-d')} {$schedule->start_time}");
            $end = Carbon::parse("{$nextDate->format('Y-m-d')} {$schedule->end_time}");

            while ($start->lt($end)) {
                $slotEnd = $start->copy()->addMinutes($schedule->slot_duration);

                DoctorSlot::firstOrCreate([
                    'doctor_id' => $doctor->id,
                    'date' => $nextDate->format('Y-m-d'),
                    'start_time' => $start->format('H:i:s'),
                ], [
                    'end_time' => $slotEnd->format('H:i:s'),
                    'is_available' => 1,
                ]);

                $start = $slotEnd;
            }
        }

        return apiResponse([], 'Schedule repeated for next week');
    }


    protected function generateSlotsForDay(Schedule $schedule)
    {
        $nextDate = $this->getDateForDay($schedule->day_of_week);

        $start = Carbon::parse("$nextDate {$schedule->start_time}");
        $end = Carbon::parse("$nextDate {$schedule->end_time}");

        while ($start->lt($end)) {
            $slotEnd = $start->copy()->addMinutes($schedule->slot_duration);

            DoctorSlot::firstOrCreate([
                'doctor_id' => $schedule->doctor_id,
                'date' => $nextDate,
                'start_time' => $start->format('H:i:s'),
            ], [
                'end_time' => $slotEnd->format('H:i:s'),
                'is_available' => 1,
            ]);

            $start = $slotEnd;
        }
    }

    protected function deleteSlotsForDay(Schedule $schedule)
    {
        DoctorSlot::where('doctor_id', $schedule->doctor_id)
            ->whereRaw('LOWER(DAYNAME(date)) = ?', [strtolower($schedule->day_of_week)])
            ->delete();
    }

    protected function getDateForDay(string $day_of_week): ?string
    {
        $today = now();
        return strtolower($today->format('l')) === strtolower($day_of_week)
            ? $today->format('Y-m-d')
            : $today->next($day_of_week)->format('Y-m-d');
    }

}

