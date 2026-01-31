<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'birthdate',
        'city',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
        ];
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'contact_department')
            ->withTimestamps();
    }

    public function scopeSearch(Builder $query, array $filters): Builder
    {
        $name = trim($filters['name']) ?? '';
        $phone = trim($filters['phone']) ?? '';
        $departmentId = $filters['department_id'] ?? null;
        return $query
            ->when(
                !empty($name),
                fn ($q) => 
                $q->where(function ($qq) use ($name) {
                    $qq->where('first_name', 'LIKE', "%{$name}%")
                    ->orWhere('last_name', 'LIKE', "%{$name}%");
                })
            )
            ->when(
                !empty($phone),
                fn ($q) => $q->where('phone_number', 'LIKE', "%{$phone}%")
            )
            ->when(
                !empty($departmentId),
                fn ($q) => $q->whereHas('departments', function ($qq) use ($departmentId) {
                    $qq->where('departments.id', $departmentId);
                })
            );
    }
 
}
