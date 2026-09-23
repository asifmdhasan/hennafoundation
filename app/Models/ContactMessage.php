<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    public const STATUSES = ['new', 'read', 'resolved'];

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
    ];
}
