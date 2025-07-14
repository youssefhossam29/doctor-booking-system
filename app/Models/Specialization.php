<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get all of the doctors for the Specialization
     */
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class, 'specialization_id');
    }
}
