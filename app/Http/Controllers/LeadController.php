<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function store(Request $request, string $slug): RedirectResponse
    {
        $profile = Profile::published()->where('slug', $slug)->firstOrFail();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:32', 'required_without:email'],
            'message' => ['required', 'string', 'max:3000'],
            'consent' => ['accepted'],
        ]);

        Lead::create($data + [
            'organization_id' => $profile->organization_id,
            'profile_id' => $profile->id,
            'submitted_at' => now(),
        ]);

        return back()->with('lead_status', 'Thank you. Your enquiry has been sent.');
    }
}
