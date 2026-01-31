<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DepartmentService
{

    public function getAllDepartments(): Collection
    {
        return Cache::remember('all_departments', 300, function () {
            $query = Department::query()
                ->orderBy('name', 'asc')
                ->get();

            return $query;
        });
    }

    public function getDepartmentsBySearch(?string $search = null): LengthAwarePaginator
    {
        $perPage = config('per_page', 5);

        return Cache::remember('searched_departments', 300, function () use ($search, $perPage) {
            return Department::query()
                ->withCount('contacts')
                ->when($search, fn ($q) => $q->where('name', 'LIKE', "%{$search}%"))
                ->orderBy('name', 'asc')
                ->paginate($perPage);
        });
    }

    public function createDepartment(array $data): Department
    {
        return DB::transaction(function () use ($data) {
            $department = Department::create($data);
            $this->clearDepartmentCache();
            
            return $department;
        });
    }

    public function updateDepartment(Department $department, array $data): Department
    {
        return DB::transaction(function () use ($department, $data) {
            $department->update($data);
            $this->clearDepartmentCache();
            
            return $department->fresh();
        });
    }

    public function deleteDepartment(Department $department): bool
    {
        $department->contacts()->detach();
        
        $result = $department->delete();
        $this->clearDepartmentCache();
        
        return $result;
    }

    public function clearDepartmentCache(): void
    {
        Cache::forget('all_departments');
        Cache::forget('searched_departments');
    }
}
