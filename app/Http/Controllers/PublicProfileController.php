<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\ProfileEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $slug): View
    {
        $profile = Profile::published()
            ->where('slug', $slug)
            ->with(['contacts', 'socialLinks', 'services', 'products', 'businessHours'])
            ->firstOrFail();

        ProfileEvent::create([
            'organization_id' => $profile->organization_id,
            'profile_id' => $profile->id,
            'event_type' => 'profile_visit',
            'visitor_hash' => hash_hmac('sha256', (string) $request->ip(), config('app.key')),
            'session_hash' => hash_hmac('sha256', (string) $request->session()->getId(), config('app.key')),
            'referrer_host' => parse_url((string) $request->headers->get('referer'), PHP_URL_HOST),
            'occurred_at' => now(),
        ]);

        return view('public.profile', compact('profile'));
    }
}
