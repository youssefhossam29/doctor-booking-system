<?php

namespace App\Http\Controllers\Api\Patient;

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
        $patientId = $user->patient->id;

        $today = Carbon::today();
        $sevenDaysAgo = Carbon::now()->subDays(6);
        $nextWeek = Carbon::now()->addWeek();

        // Appointments
        $totalAppointments = Appointment::where('patient_id', $patientId)->count();

        $appointmentsToday = Appointment::where('patient_id', $patientId)
            ->whereDate('appointment_date', $today)->count();

        $appointmentsLast7Days = Appointment::where('patient_id', $patientId)
            ->whereBetween('appointment_date', [$sevenDaysAgo, $today->endOfDay()])
            ->count();

        $appointmentsNextWeek = Appointment::where('patient_id', $patientId)
            ->whereBetween('appointment_date', [$today, $nextWeek])->count();

        $completedAppointments = Appointment::where('patient_id', $patientId)
            ->where('status', 'completed')->count();

        $cancelledAppointments = Appointment::where('patient_id', $patientId)
            ->where('status', 'cancelled')->count();

        $cancellationRate = $totalAppointments > 0
            ? round(($cancelledAppointments / $totalAppointments) * 100, 2)
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
        $latestAppointments = Appointment::with('doctor.user')
            ->where('patient_id', $patientId)
            ->latest()->take(3)->get();

        $latestContacts = Contact::with('replies')
            ->where('user_id', $userId)
            ->latest()->take(3)->get();

        return apiResponse([
            'appointments' => [
                'total' => $totalAppointments,
                'today' => $appointmentsToday,
                'last_7_days' => $appointmentsLast7Days,
                'next_7_days' => $appointmentsNextWeek,
                'completed' => $completedAppointments,
                'cancelled' => $cancelledAppointments,
                'cancellation_rate_percent' => $cancellationRate . "%",
            ],
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
        ], 'Patient dashboard statistics fetched successfully.', 200);
    }
}
