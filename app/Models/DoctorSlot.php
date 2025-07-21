<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSlot extends Model
{
    use HasFactory;

    protected $fillable = ['doctor_id', 'date', 'start_time', 'end_time', 'is_available'];

    /**
     * Get the doctor that owns the Slot
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}
