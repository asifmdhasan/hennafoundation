<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsPublicSiteSettings;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ContactMessageController extends Controller
{
    use LoadsPublicSiteSettings;

    public function create(): View
    {
        return view('forms.contact-us', ['settings' => $this->publicSiteSettings()]);
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        ContactMessage::query()->create([
            ...$request->validated(),
            'status' => 'new',
        ]);

        return redirect()->route('contact-us.create')->with('form_success', 'Thank you. Your message has been received.');
    }
}
