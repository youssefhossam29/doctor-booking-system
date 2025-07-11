<?php

namespace App\Enums;

enum UserType: int
{
    case PATIENT = 1;
    case DOCTOR = 2;
    case ADMIN = 3;
}
