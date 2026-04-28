<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'focal_person_name',
        'desk_number',
        'contact_details',
        'department_desk',
        'office_hours',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(PesoProgram::class, 'program_id');
    }
}
