<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Http\Resources\ContactCollection;
use App\Http\Requests\SearchContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(
        private readonly ContactService $contactService
    ) {}


    public function index(SearchContactRequest $request): ContactCollection
    {
        if ($request->boolean('all')) {

            $contacts = $this->contactService->getContacts();
            return new ContactCollection($contacts);
        }

        $filters = $request->only(['name', 'phone', 'department_id']);

        $contacts = $this->contactService->searchContacts($filters);

        return new ContactCollection($contacts);
    }

    public function store(StoreContactRequest $request): JsonResponse
    {
        $contact = $this->contactService->createContact($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Contact created successfully',
            'data' => new ContactResource($contact),
        ]);
    }

    public function show(Contact $contact): JsonResponse
    {
        $contact->load('departments');

        return response()->json([
            'success' => true,
            'data' => new ContactResource($contact),
        ]);
    }

    public function update(UpdateContactRequest $request, Contact $contact): JsonResponse
    {
        $contact = $this->contactService->updateContact($contact, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Contact updated successfully',
            'data' => new ContactResource($contact),
        ]);
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $this->contactService->deleteContact($contact);

        return response()->json([
            'success' => true,
            'message' => 'Contact deleted successfully',
        ]);
    }

    public function importContact(Request $request): JsonResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            
            $header = array_shift($csvData);
            
            $mappedData = array_map(function ($row) use ($header) {
                return array_combine($header, $row);
            }, $csvData);

            $data = $this->contactService->importFromCsv($mappedData);

            return response()->json([
                'success' => true,
                'message' => "{$data['imported']} contacts imported and {$data['updated']} updated successfully",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import contacts',
                'error' => $e->getMessage(),
            ]);
        }
    }

}
