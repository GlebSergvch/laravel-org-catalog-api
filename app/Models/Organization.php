<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'inn',
        'phone',
        'description',
        'created_by',
        'updated_by',
    ];

    public function buildings(): BelongsToMany
    {
        return $this->belongsToMany(Building::class, 'organization_building')
            ->withPivot('is_head_office', 'opened_at', 'closed_at', 'created_by', 'updated_by')
            ->withTimestamps();
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'organization_activity')
            ->withPivot('created_by', 'updated_by')
            ->withTimestamps();
    }

    public function phones()
    {
        return $this->hasMany(OrganizationPhone::class);
    }

    public function mainPhone()
    {
        return $this->hasOne(OrganizationPhone::class)->where('is_main', true);
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
