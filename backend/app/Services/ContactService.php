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
        return Cache::remember('all_contacts', 300, function () use ($perPage) {
            Contact::query()
                ->with('departments:id,name')
                ->orderBy('first_name', 'asc')
                ->paginate($perPage);
            });
    }

    public function searchContacts(
        array $filters,
    ): LengthAwarePaginator {

        $perPage = config('per_page', 5);
        return Cache::remember('searched_contacts', 300, function () use ($filters, $perPage) {
            return $this->applySearch($filters, $perPage);
        });
    }

    /**
     * Execute the search query.
     */
    private function applySearch(array $filters, int $perPage): LengthAwarePaginator
    {
        return Contact::query()
            ->with('departments:id,name')
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

            return $contact->load('departments:id,name');
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

            return $contact->fresh(['departments:id,name']);
        });
    }

    public function deleteContact(Contact $contact): bool
    {
        $result = $contact->delete();
        $this->clearContactCache();
        
        return $result;
    }

    public function getContactById(int $id): ?Contact
    {
        return Contact::with('departments:id,name')->find($id);
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
