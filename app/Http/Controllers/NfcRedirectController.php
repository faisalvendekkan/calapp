<?php

namespace App\Http\Controllers;

use App\Models\NfcCard;
use App\Models\ProfileEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NfcRedirectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $card = NfcCard::query()
            ->where('secure_token', $token)
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->with('profile')
            ->firstOrFail();

        abort_unless($card->profile && $card->profile->status === 'published', 404);

        ProfileEvent::create([
            'organization_id' => $card->profile->organization_id,
            'profile_id' => $card->profile->id,
            'event_type' => 'nfc_tap',
            'visitor_hash' => hash_hmac('sha256', (string) $request->ip(), config('app.key')),
            'occurred_at' => now(),
        ]);

        return redirect()->route('public.profile', $card->profile->slug);
    }
}
