<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\SpecializationResource;
use App\Models\Appointment;
use App\Models\Contact;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Specialization;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $last7Days = Carbon::now()->subDays(7);

        // Basic Counts
        $doctorsCount = Doctor::count();
        $patientsCount = Patient::count();
        $specializationsCount = Specialization::count();
        $contactsCount = Contact::count();
        $openContactsCount = Contact::where('status', 'open')->count();
        $closedContactsCount = Contact::where('status', 'closed')->count();

        // Appointments
        $todayAppointmentsCount = Appointment::whereDate('appointment_date', $today)->count();
        $weekAppointmentsCount = Appointment::whereBetween('appointment_date', [$weekStart, $weekEnd])->count();
        $cancelledToday = Appointment::whereDate('appointment_date', $today)->where('status', 'cancelled')->count();
        $cancelledWeek = Appointment::whereBetween('appointment_date', [$weekStart, $weekEnd])->where('status', 'cancelled')->count();
        $completedToday = Appointment::whereDate('appointment_date', $today)->where('status', 'completed')->count();
        $completedWeek = Appointment::whereBetween('appointment_date', [$weekStart, $weekEnd])->where('status', 'completed')->count();

        $totalAppointments = Appointment::count();
        $totalCancelled = Appointment::where('status', 'cancelled')->count();
        $cancellationRate = $totalAppointments > 0
            ? round(($totalCancelled / $totalAppointments) * 100, 2)
            : 0;

        // Users created last 7 days
        $patientsLast7Days = Patient::whereDate('created_at', '>=', $last7Days)->count();
        $doctorsLast7Days = Doctor::whereDate('created_at', '>=', $last7Days)->count();

        // No doctors in specialization
        $specializationsWithoutDoctors = Specialization::doesntHave('doctors')->count();

        // Patients without appointments
        $patientsWithoutAppointments = Patient::doesntHave('appointments')->count();

        // Doctors without appointments
        $doctorsWithoutAppointments = Doctor::doesntHave('appointments')->count();

        // Contacts without reply
        $contactsWithoutReply = Contact::where('status', 'open')
            ->whereDoesntHave('replies')
            ->count();

        // Latest 3 records
        $latestContacts = Contact::with('user')->latest()->take(3)->get();
        $latestDoctors = Doctor::with('user', 'specialization')->latest()->take(3)->get();
        $latestPatients = Patient::with('user')->latest()->take(3)->get();
        $latestSpecializations = Specialization::latest()->take(3)->get();

        // Top three specializations by appointment count
        $topDoctors = Doctor::with('user', 'specialization')
            ->withCount('appointments')
            ->orderByDesc('appointments_count')
            ->take(3)->get();


        return apiResponse([
            'counts' => [
                'doctors' => $doctorsCount,
                'patients' => $patientsCount,
                'appointments_today' => $todayAppointmentsCount,
                'appointments_this_week' => $weekAppointmentsCount,
                'specializations' => $specializationsCount,
                'contacts' => $contactsCount,
                'contacts_open' => $openContactsCount,
                'contacts_closed' => $closedContactsCount,
                'patients_last_7_days' => $patientsLast7Days,
                'doctors_last_7_days' => $doctorsLast7Days,
            ],
            'appointments' => [
                'completed_today' => $completedToday,
                'completed_week' => $completedWeek,
                'cancelled_today' => $cancelledToday,
                'cancelled_week' => $cancelledWeek,
                'cancellation_rate_percent' => $cancellationRate . "%",
            ],
            'latest' => [
                'contacts' => ContactResource::collection($latestContacts),
                'doctors' => DoctorResource::collection($latestDoctors),
                'patients' => PatientResource::collection($latestPatients),
                'specializations' => SpecializationResource::collection($latestSpecializations),
            ],
            'top' => [
                'doctors' => DoctorResource::collection($topDoctors),
            ],
            'warnings' => [
                'specializations_without_doctors' => $specializationsWithoutDoctors,
                'patients_without_appointments' => $patientsWithoutAppointments,
                'doctors_without_appointments' => $doctorsWithoutAppointments,
                'contacts_without_reply' => $contactsWithoutReply,
            ]
        ], "Dashboard statistics fetched successfully.", 200);
    }
}
