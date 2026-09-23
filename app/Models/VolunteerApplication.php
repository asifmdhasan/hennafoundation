<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolunteerApplication extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'reviewed', 'approved', 'rejected'];

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'home_telephone',
        'address_1',
        'address_2',
        'city',
        'postal_code',
        'status',
    ];
}
