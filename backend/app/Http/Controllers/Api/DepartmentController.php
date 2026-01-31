<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(
        private readonly DepartmentService $departmentService
    ) {}


    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('all')) {
            $departments = $this->departmentService->getAllDepartments();

            return response()->json([
                'success' => true,
                'data' => $departments,
            ]);
        }

        $perPage = $request->input('per_page');
        $search = $request->input('search');

        $departments = $this->departmentService->getDepartmentsBySearch($perPage, $search);

        return response()->json([
                'success' => true,
                'data' => $departments,
        ]);
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->departmentService->createDepartment($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Department created successfully',
            'data' => $department,
        ], 201);
    }

    public function show(Department $department): JsonResponse
    {
        $department->loadCount('contacts');

        return response()->json([
            'success' => true,
            'data' => $department,
        ]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $department = $this->departmentService->updateDepartment($department, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully',
            'data' => $department,
        ]);
    }

    public function destroy(Department $department): JsonResponse
    {
        $this->departmentService->deleteDepartment($department);

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully',
        ]);
    }

}
