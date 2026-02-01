<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ContactService
{
    public function getContacts(): LengthAwarePaginator {

        $perPage = config('per_page', 5);
        $page = request()->get('page', 1);
        $cacheKey = 'all_contacts:' . md5(json_encode([
            'page'    => $page,
            'perPage' => $perPage,
        ]));
        return Cache::remember($cacheKey, 300, function () use ($perPage) {
            return Contact::query()
                ->with('departments')
                ->orderBy('first_name', 'asc')
                ->paginate($perPage);
            });
    }

    public function searchContacts(
        array $filters,
    ): LengthAwarePaginator {

        $perPage = config('per_page', 5);
        $page = request()->get('page', 1);

        $cacheKey = 'searched_contacts:' . md5(json_encode([
            'filters' => $filters,
            'page'    => $page,
            'perPage' => $perPage,
        ]));
        return Cache::remember($cacheKey, 300, function () use ($filters, $perPage) {
            return $this->applySearch($filters, $perPage);
        });
    }

    /**
     * Execute the search query.
     */
    private function applySearch(array $filters, int $perPage): LengthAwarePaginator
    {
        return Contact::query()
            ->with('departments')
            ->search($filters)
            ->orderBy('first_name', 'asc')
            ->paginate($perPage);
    }

    public function createContact(array $data): Contact
    {
        return DB::transaction(function () use ($data) {
            $departmentIds = $data['department_ids'] ?? [];
            unset($data['department_ids']);

            $contact = Contact::create($data);

            if (!empty($departmentIds)) {
                $contact->departments()->sync($departmentIds);
            }

            $this->clearContactCache();

            return $contact->load('departments');
        });
    }

    public function updateContact(Contact $contact, array $data): Contact
    {
        return DB::transaction(function () use ($contact, $data) {
            $departmentIds = $data['department_ids'] ?? null;
            unset($data['department_ids']);

            $contact->update($data);

            if ($departmentIds !== null) {
                $contact->departments()->sync($departmentIds);
            }

            $this->clearContactCache();

            return $contact->fresh('departments');
        });
    }

    public function deleteContact(Contact $contact): bool
    {
        $contact->departments()->detach();
        $result = $contact->delete();
        $this->clearContactCache();
        
        return $result;
    }

    public function importFromCsv(array $csvData, array $defaultDepartmentIds = []): int
    {
        $imported = 0;

        DB::transaction(function () use ($csvData, $defaultDepartmentIds, &$imported) {
            foreach ($csvData as $row) {
                $contact = Contact::create([
                    'first_name' => $row['first_name'] ?? $row['First name'] ?? '',
                    'last_name' => $row['last_name'] ?? $row['Last Name'] ?? '',
                    'phone_number' => $row['phone_number'] ?? $row['Phone Number'] ?? '',
                    'birthdate' => $row['birthdate'] ?? $row['Birthdate'] ?? null,
                    'city' => $row['city'] ?? $row['City'] ?? null,
                    'is_active' => true,
                ]);

                if (!empty($defaultDepartmentIds)) {
                    $contact->syncDepartments($defaultDepartmentIds);
                }

                $imported++;
            }
        });

        $this->clearContactCache();

        return $imported;
    }

    public function clearContactCache(): void
    {
        Cache::forget('all_contacts');
        Cache::forget('searched_contacts');
    }

}
