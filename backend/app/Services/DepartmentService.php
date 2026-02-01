<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DepartmentService
{

    public function getAllDepartments()
    {
        return Cache::remember('all_departments', 300, function () {
            $query = Department::query()
                ->select('id', 'name')
                ->orderBy('name', 'asc')
                ->get();

            return $query;
        });
    }

    public function getDepartmentsBySearch(?string $search = null): LengthAwarePaginator
    {
        $perPage = config('per_page', 5);
        $page = request()->get('page', 1);

        $cacheKey = 'searched_departments:' . md5(json_encode([
            'search' => $search,
            'page'    => $page,
            'perPage' => $perPage,
        ]));
        return Cache::remember($cacheKey, 300, function () use ($search, $perPage) {
            return Department::query()
                ->with('contacts')
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
            
            return $department->fresh('contacts');
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
