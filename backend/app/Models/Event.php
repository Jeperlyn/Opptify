<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'description',
        'event_date',
        'send_email_alert',
        'send_sms_alert',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'send_email_alert' => 'boolean',
        'send_sms_alert' => 'boolean',
    ];
}
