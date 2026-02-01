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

}
