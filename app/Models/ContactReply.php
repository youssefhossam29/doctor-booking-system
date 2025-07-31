<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Enums\UserType;

class ContactReply extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'contact_id', 'user_type', 'message'];

    protected $casts = [
        'user_type' => UserType::class,
    ];

    /**
     * Get the user that sent the reply
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the contact that owns the ContactReply
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    /**
     * Get the readable type name of the user.
     */
    public function getUserTypeNameAttribute(): string
    {
        return $this->user_type->name;
    }
}
