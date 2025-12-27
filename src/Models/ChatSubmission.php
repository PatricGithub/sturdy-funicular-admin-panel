<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSubmission extends Model
{
    protected $fillable = [
        'move_type',
        'move_size',
        'move_date',
        'address_from',
        'address_to',
        'name',
        'email',
        'phone',
        'preferred_contact',
        'source',
        'status',
        'notes',
    ];
}
