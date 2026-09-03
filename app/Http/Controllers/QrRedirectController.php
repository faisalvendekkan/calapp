<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\ProfileEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QrRedirectController extends Controller
{
    public function __invoke(Request $request, string $uuid): RedirectResponse
    {
        $profile = Profile::published()->where('uuid', $uuid)->firstOrFail();

        ProfileEvent::create([
            'organization_id' => $profile->organization_id,
            'profile_id' => $profile->id,
            'event_type' => 'qr_scan',
            'visitor_hash' => hash_hmac('sha256', (string) $request->ip(), config('app.key')),
            'session_hash' => hash_hmac('sha256', (string) $request->session()->getId(), config('app.key')),
            'occurred_at' => now(),
        ]);

        return redirect()->route('public.profile', $profile->slug);
    }
}
