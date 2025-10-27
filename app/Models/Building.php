<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Building extends Model
{
    protected $fillable = [
        'address',
        'city',
        'location',
        'created_by',
        'updated_by',
    ];

    // Каст для POINT → массив [lng, lat]
//    protected $casts = [
//        'location' => 'array',
//    ];

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_building')
            ->withPivot('is_head_office', 'opened_at', 'closed_at', 'created_by', 'updated_by')
            ->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
