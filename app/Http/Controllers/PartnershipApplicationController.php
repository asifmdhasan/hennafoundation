<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsPublicSiteSettings;
use App\Http\Requests\StorePartnershipApplicationRequest;
use App\Models\PartnershipApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PartnershipApplicationController extends Controller
{
    use LoadsPublicSiteSettings;

    public function create(): View
    {
        return view('forms.partnership-registration', ['settings' => $this->publicSiteSettings()]);
    }

    public function store(StorePartnershipApplicationRequest $request): RedirectResponse
    {
        PartnershipApplication::query()->create([
            ...$request->validated(),
            'status' => 'pending',
        ]);

        return redirect()->route('partnership-registration.create')->with('form_success', 'Thank you. Your partnership registration has been received.');
    }
}
