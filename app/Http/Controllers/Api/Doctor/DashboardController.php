<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\ContactResource;
use App\Models\Appointment;
use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserType;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;
        $doctorId = $user->doctor->id;

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $sevenDaysAgo = Carbon::now()->subDays(6);
        $weekStart = Carbon::now();
        $weekEnd = Carbon::now()->addWeek();

        // Appointments
        $appointmentsToday = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)->count();

        $appointmentsYesterday = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $yesterday)->count();

        $appointmentsLast7Days = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('appointment_date', [$sevenDaysAgo, $today->endOfDay()])
            ->count();

        $appointmentsNextWeek = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('appointment_date', [$weekStart, $weekEnd])->count();

        $patientsSeen = Appointment::where('doctor_id', $doctorId)
            ->distinct('patient_id')->count('patient_id');

        $completedToday = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->where('status', 'completed')->count();

        $completedLast7Days = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('appointment_date', [$sevenDaysAgo, $today->endOfDay()])
            ->where('status', 'completed')->count();

        $cancelledToday = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->where('status', 'cancelled')->count();

        $cancelledLast7Days = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('appointment_date', [$sevenDaysAgo, $today->endOfDay()])
            ->where('status', 'cancelled')->count();

        $totalAppointments = Appointment::where('doctor_id', $doctorId)->count();
        $totalCancelled = Appointment::where('doctor_id', $doctorId)
            ->where('status', 'cancelled')->count();

        $cancellationRate = $totalAppointments > 0
            ? round(($totalCancelled / $totalAppointments) * 100, 2)
            : 0;

        // Contacts
        $contactsCount = Contact::where('user_id', $userId)->count();
        $openContactsCount = Contact::where('user_id', $userId)->where('status', 'open')->count();
        $closedContactsCount = Contact::where('user_id', $userId)->where('status', 'closed')->count();

        // Contacts without admin reply
        $contactsWithoutAdminReplyCount = Contact::where('user_id', $userId)
            ->where('status', 'open')
            ->whereDoesntHave('replies', function ($query) {
                $query->where('user_type', UserType::ADMIN);
            })->count();

        // Latest
        $latestAppointments = Appointment::with('patient.user')
            ->where('doctor_id', $doctorId)
            ->latest()->take(3)->get();

        $latestContacts = Contact::with('replies')
            ->where('user_id', $userId)
            ->latest()->take(3)->get();

        return apiResponse([
            'appointments' => [
                'today' => $appointmentsToday,
                'yesterday' => $appointmentsYesterday,
                'last_7_days' => $appointmentsLast7Days,
                'next_7_days' => $appointmentsNextWeek,
                'completed_today' => $completedToday,
                'completed_last_7_days' => $completedLast7Days,
                'cancelled_today' => $cancelledToday,
                'cancelled_last_7_days' => $cancelledLast7Days,
                'cancellation_rate_percent' => $cancellationRate . "%",
            ],
            'patients_count' => $patientsSeen,
            'contacts' => [
                'total' => $contactsCount,
                'open' => $openContactsCount,
                'closed' => $closedContactsCount,
                'open_without_admin_reply' => $contactsWithoutAdminReplyCount,
            ],
            'latest' => [
                'appointments' => AppointmentResource::collection($latestAppointments),
                'contacts' => ContactResource::collection($latestContacts),
            ],
        ], 'Doctor dashboard statistics fetched successfully.', 200);
    }
}
