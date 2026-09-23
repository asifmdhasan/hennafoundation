<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsPublicSiteSettings;
use App\Http\Requests\StoreVolunteerApplicationRequest;
use App\Models\VolunteerApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VolunteerApplicationController extends Controller
{
    use LoadsPublicSiteSettings;

    public function create(): View
    {
        return view('forms.volunteer-registration', ['settings' => $this->publicSiteSettings()]);
    }

    public function store(StoreVolunteerApplicationRequest $request): RedirectResponse
    {
        VolunteerApplication::query()->create([
            ...$request->validated(),
            'status' => 'pending',
        ]);

        return redirect()->route('volunteer-registration.create')->with('form_success', 'Thank you. Your volunteer registration has been received.');
    }
}
