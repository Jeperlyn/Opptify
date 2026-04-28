<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'wants_job_fair',
        'wants_employer_of_the_day',
    ];

    protected $casts = [
        'wants_job_fair' => 'boolean',
        'wants_employer_of_the_day' => 'boolean',
    ];
}