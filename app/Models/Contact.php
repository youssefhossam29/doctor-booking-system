<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Enums\UserType;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'user_type', 'subject', 'message', 'status'];

    protected $casts = [
        'user_type' => UserType::class,
    ];

    /**
     * Get the user that owns the Contact
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all of the replies for the Contact
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies(): HasMany
    {
        return $this->hasMany(ContactReply::class);
    }

    /**
     * Get the readable type name of the user.
     */
    public function getUserTypeNameAttribute(): string
    {
        return $this->user_type->name;
    }
}
