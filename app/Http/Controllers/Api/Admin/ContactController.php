<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

use App\Http\Resources\ContactResource;
use App\Http\Resources\ContactReplyResource;

use App\Models\Contact;
use App\Models\ContactReply;
use App\Models\User;
use App\Enums\UserType;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:open,closed',
        ]);

        if ($validator->fails()) {
            return apiResponse("Validation error", $validator->errors(), 422);
        }

        $query = Contact::with('user')->latest();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('status') && in_array($request->status, ['open', 'closed'])) {
            $query->where('status', $request->status);
        }

        $contacts = $query->get();

        if ($contacts->isEmpty()) {
            return apiResponse([], "No contacts found.", 200);
        }

        $contacts = ContactResource::collection($contacts);
        return apiResponse($contacts, 'Contacts fetched successfully');
    }


    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return apiResponse("Validation error", $validator->errors(), 422);
        }

        $search = $request->input('search');

        $contacts = Contact::with('user')
            ->where(function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->orWhere('subject', 'LIKE', "%{$search}%")
                ->orWhere('message', 'LIKE', "%{$search}%");
            })
            ->latest()
            ->get();

        if ($contacts->isEmpty()) {
            return apiResponse([], "No results found for: $search", 200);
        }

        $contacts = ContactResource::collection($contacts);
        return apiResponse($contacts, "Search results for: $search", 200);
    }

    public function replies(Contact $contact)
    {
        $contact->load(['user', 'replies.user']);
        $replies = ContactReplyResource::collection($contact->replies);
        $contact = new ContactResource($contact);

        return apiResponse([
            'contact' => $contact,
            'replies' => $replies
        ], "replies for {$contact->subject} fetched successfully");
    }

    public function reply(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $reply = ContactReply::create([
            'contact_id' => $contact->id,
            'user_id'    => $request->user()->id,
            'user_type'  => UserType::ADMIN,
            'message'    => $validated['message'],
        ]);

        $reply = new ContactReplyResource($reply);
        return apiResponse($reply, 'Reply added successfully');
    }

    public function show(Contact $contact)
    {
        $contact = new ContactResource($contact);

        return apiResponse($contact, 'Contact fetched successfully');
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,closed',
        ]);

        $contact->update($validated);
        $contact = new ContactResource($contact);

        return apiResponse($contact, 'Contact updated successfully');
    }

}



