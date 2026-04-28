<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PesoProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'requirements',
        'steps_to_avail',
        'category',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'requirements' => 'array',
        'steps_to_avail' => 'array',
        'is_active' => 'boolean',
    ];

    public function contact(): HasOne
    {
        return $this->hasOne(ProgramContact::class, 'program_id');
    }
}
