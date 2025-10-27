<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'level',
        'created_by',
        'updated_by',
    ];

    // === Связи ===

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Activity::class, 'parent_id');
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'activity_organization')
            ->withPivot('created_by', 'updated_by')
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

    // === Валидация уровня при сохранении ===
    protected static function booted()
    {
        static::saving(function ($activity) {
            if ($activity->parent_id) {
                $parent = $activity->parent;
                if ($parent && $parent->level >= 3) {
                    throw new \Exception('Нельзя добавить дочерний элемент на уровне 4 и выше');
                }
                $activity->level = $parent->level + 1;
            } else {
                $activity->level = 1;
            }
        });
    }
}
