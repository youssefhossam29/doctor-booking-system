# 🩺 Doctor Booking System

This is a Laravel 10-based project that provides a complete **doctor appointment booking system** with full **role-based multi-auth** architecture. Built with scalability and clean code principles in mind, the system allows three main user types—**Admin**, **Doctor**, and **Patient**—to interact with the application through well-defined, secured APIs.

It handles real-world logic such as managing doctor schedules, generating appointment slots, booking and updating appointments, and securing data access through user roles and middleware. The system is API-first and ready to power mobile or web clients.

The project includes a contact module where **patients**, **doctors**, and **admins** can interact through structured messages and replies.


---

## 🔍 Overview

**Doctor Booking System** is a RESTful Laravel application designed to manage:

- 🧑‍⚕️ **Doctor–Patient appointment bookings**
- 📅 **Doctor schedules and dynamic slot generation**
- 🔐 **Role-based access control** using enums and middleware
- 🧑‍💼 **Admin-driven control** over doctors, patients, specializations, appointments, and contact requests
- ✅ **Custom request validation** and conflict checking for appointment logic
- 🔁 **Automatic slot availability** updates on booking and cancellation
- 🔒 **API authentication & authorization** powered by Laravel Sanctum and Policies
- 📂 **Modular and clean controller structure** for scalability and testing
- 💬 **Contact/Support system** allowing users to submit and reply to support messages

---

## 🚀 Features

### 🔐 Authentication & Authorization

- Multi-role authentication (Patient, Doctor, Admin)  
- Change password, update data, and delete account for all users (where applicable)  
- Middleware-based role access control  
- **Authorization via Laravel Policies** to restrict access to resources like appointments—ensuring only the owner (patient or doctor) can view, update, or cancel

---

### 👨‍💼 Admin Features

- **Account Management**
  - Login, logout, change password
  - View and update own account (excluding specialization)
  - Delete own account

- **Doctor Management**
  - Add, update (with specialization), delete, show one/all
  - Search doctors by name or specialization

- **Specialization Management**
  - Add, update, delete, show one/all
  - List doctors under a specific specialization

- **Patient Management**
  - Add, update, delete, show one/all
  - Search patients by name

- **Schedule Management**
  - Show doctor schedules
  - Add schedules with generated slots
  - Update/delete schedule and related slots
  - Repeat weekly schedule for a doctor

- **Slot Management**
  - Show slots by doctor and date
  - Delete all slots by doctor and date
  - Delete specific slot

- **Appointment Management**
  - Add/update/delete appointments
  - View single or all appointments
  - Filter appointments by doctor/patient/date
  - Prevents double-booking and ensures slot availability
  - Search appointments by doctor/patient name

- **Contact Management**
  - View all contact requests (with filters for status/user)
  - Search contact requests by user name, email, subject, or message
  - View specific contact and all related replies
  - Reply to user messages
  - Update status (`open` or `closed`) for any contact

---

### 🩺 Doctor Features

- **Account Management**
  - Login, logout, change password
  - View and update own account (excluding specialization)
  - Delete own account

- **Schedule Management**
  - Create, view, update, and delete own schedule
  - Repeat weekly schedule

- **Slot Management**
  - View available slots by date
  - Delete all or specific slots for a given date

- **Patient Management**
  - View list of patients who have appointments with him
  - View individual patient account
  - Search patients by name

- **Appointment Management**
  - View all appointments with patients
  - Filter/search appointments by name or date
  - Update or cancel appointments

- **Contact System**
  - Submit new contact requests to the admin
  - View own contact requests (filter by status)
  - View specific contact and all related replies
  - Search own contact requests by user subject, or message
  - Reply to admin messages

---

### 👤 Patient Features

- **Account Management**
  - Register, login, logout
  - Change password
  - View, update, or delete own account

- **Doctor & Specialization**
  - Show all doctors
  - View doctor profile and doctor’s schedule
  - Search for doctor by name or specialization
  - Show all specializations
  - Search specializations
  - List doctors under a specific specialization

- **Appointment Management**
  - View all or specific appointments with doctors
  - Filter/search appointments
  - Add, update, cancel appointment

- **Contact System**
  - Submit new contact requests to the admin
  - View own contact requests (filter by status)
  - View specific contact and all related replies
  - Search own contact requests by user subject, or message
  - Reply to admin messages
  
---

## 🧱 Technologies

- PHP 8.1+  
- Laravel 10  
- MySQL  
- Laravel Sanctum  

---

## 🧑‍💻 User Roles

Users are assigned a `type` stored in the `users` table and represented via enum:

```php
// app/Enums/UserType.php

namespace App\Enums;

enum UserType: int
{
    case PATIENT = 1;
    case DOCTOR = 2;
    case ADMIN = 3;
}
````

---

## 🛠 Getting Started

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/doctor-booking-system.git
cd doctor-booking-system
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit the `.env` file with your database and mail settings.

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Seed the Database (Optional)

```bash
php artisan db:seed --class=DataSeeder
```

This will generate example doctors, patients, specializations, and appointments.

### 6. Serve the Application

```bash
php artisan serve
```

---

## 🔐 Multi-Auth Implementation

### 1. Enum-based Roles

See `App\Enums\UserType`. All users are distinguished using a single `type` column in the `users` table.

### 2. Role Middleware

The `CheckTypes` middleware restricts route access by mapping role names (e.g., `'admin'`, `'doctor'`, `'patient'`) to `UserType` enum values and aborts with a 403 if the authenticated user’s type is not allowed.

### 3. API Route Structure

Protected using `auth:sanctum` and `CheckTypes` middleware in `api.php`.

```php
// Role-based routes
Route::prefix('admin')->middleware(['auth:sanctum', 'CheckTypes:admin'])->group(...);
Route::prefix('doctor')->middleware(['auth:sanctum', 'CheckTypes:doctor'])->group(...);
Route::prefix('patient')->middleware(['auth:sanctum', 'CheckTypes:patient'])->group(...);
```

---

## 📬 Contribution

Feel free to fork the project and submit pull requests. Suggestions, bug fixes, and improvements are always welcome!

---
