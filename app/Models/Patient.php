<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date_of_birth',
        'image',
        'gender',
        'phone',
    ];

    /**
     * Get the user that owns the Patient
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all of the appointments for the Patient
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function getGenderTypeAttribute()
    {
        if ($this->gender === null) {
            return 'Not selected';
        } elseif ($this->gender == 1) {
            return 'Male';
        } elseif ($this->gender == 0) {
            return 'Female';
        }
        return 'Not selected';
    }
}
