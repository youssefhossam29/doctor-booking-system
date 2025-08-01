<?php

namespace App\Http\Controllers\Api\Doctor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Contact;
use App\Models\ContactReply;
use App\Http\Resources\ContactResource;
use App\Http\Resources\ContactReplyResource;
use App\Enums\UserType;

class ContactController extends Controller
{
    //
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:open,closed',
        ]);

        if ($validator->fails()) {
            return apiResponse("Validation error", $validator->errors(), 422);
        }

        $query = Contact::with('user')
            ->where('user_id', $request->user()->id)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $contacts = $query->get();

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

        $contacts = Contact::with('user')->where('user_id', $request->user()->id)
            ->where(function ($query) use ($search) {
                $query->where('subject', 'LIKE', "%{$search}%")
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $contact = Contact::create([
            'user_id' => $request->user()->id,
            'user_type' => $request->user()->type,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status'  => 'open',
        ]);

        $contact = new ContactResource($contact);
        return apiResponse($contact, 'Contact fetched successfully');
    }

    public function show(Contact $contact)
    {
        $this->authorize('view', $contact);

        $contact = new ContactResource($contact);
        return apiResponse($contact, 'Contact fetched successfully');
    }

    public function replies(Contact $contact)
    {
        $this->authorize('replies', $contact);

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
        $this->authorize('reply', $contact);

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $reply = ContactReply::create([
            'contact_id' => $contact->id,
            'user_id'    => $request->user()->id,
            'user_type'  => UserType::DOCTOR,
            'message'    => $validated['message'],
        ]);

        $reply = new ContactReplyResource($reply);
        return apiResponse($reply, 'Reply added successfully');
    }
}
