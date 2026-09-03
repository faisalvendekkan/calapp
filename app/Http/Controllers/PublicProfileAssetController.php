<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\ProfileEvent;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicProfileAssetController extends Controller
{
    public function vcard(Request $request, string $slug): Response
    {
        $profile = $this->publishedProfile($slug);
        $contacts = $profile->contacts()->where('is_visible', true)->get()->keyBy('type');

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'FN:'.$this->escapeVcard($profile->name),
        ];

        if ($profile->company_name) {
            $lines[] = 'ORG:'.$this->escapeVcard($profile->company_name);
        }
        if ($profile->designation) {
            $lines[] = 'TITLE:'.$this->escapeVcard($profile->designation);
        }
        if ($phone = $contacts->get('mobile') ?? $contacts->get('telephone')) {
            $lines[] = 'TEL;TYPE=CELL:'.$this->escapeVcard($phone->value);
        }
        if ($email = $contacts->get('email')) {
            $lines[] = 'EMAIL;TYPE=INTERNET:'.$this->escapeVcard($email->value);
        }
        if ($website = $contacts->get('website')) {
            $lines[] = 'URL:'.$this->escapeVcard($website->url ?: $website->value);
        }
        if ($address = $contacts->get('address')) {
            $lines[] = 'ADR;TYPE=WORK:;;'.$this->escapeVcard($address->value).'||||';
        }
        $lines[] = 'END:VCARD';

        $this->record($request, $profile, 'contact_save');

        return response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/vcard; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$profile->slug.'.vcf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function qr(Request $request, string $slug): Response
    {
        $profile = $this->publishedProfile($slug);
        $writer = new Writer(new ImageRenderer(new RendererStyle(1024, 4), new SvgImageBackEnd));
        $svg = $writer->writeString(route('qr.redirect', $profile->uuid));

        if ($request->boolean('download')) {
            $this->record($request, $profile, 'qr_download');
        }

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=utf-8',
            'Content-Disposition' => ($request->boolean('download') ? 'attachment' : 'inline').'; filename="'.$profile->slug.'-qr.svg"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function contact(Request $request, string $slug, string $action): RedirectResponse
    {
        $profile = $this->publishedProfile($slug);
        $allowed = ['mobile' => 'call_click', 'telephone' => 'call_click', 'whatsapp' => 'whatsapp_click', 'email' => 'email_click', 'website' => 'website_click', 'directions' => 'direction_click'];

        abort_unless(isset($allowed[$action]), 404);
        $contact = $profile->contacts()->where('type', $action)->where('is_visible', true)->firstOrFail();
        $target = $this->contactTarget($action, $contact->url ?: $contact->value);
        $this->record($request, $profile, $allowed[$action], ['contact_type' => $action]);

        return redirect()->away($target);
    }

    public function social(Request $request, string $slug, string $network): RedirectResponse
    {
        $profile = $this->publishedProfile($slug);
        $social = $profile->socialLinks()->where('network', $network)->where('is_visible', true)->firstOrFail();
        $target = $this->safeWebUrl($social->url);
        $this->record($request, $profile, 'social_click', ['network' => $network]);

        return redirect()->away($target);
    }

    private function publishedProfile(string $slug): Profile
    {
        return Profile::published()->where('slug', $slug)->firstOrFail();
    }

    private function contactTarget(string $type, string $value): string
    {
        return match ($type) {
            'mobile', 'telephone' => 'tel:'.preg_replace('/[^0-9+]/', '', $value),
            'whatsapp' => 'https://wa.me/'.preg_replace('/\D/', '', $value),
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) ? 'mailto:'.$value : throw new NotFoundHttpException,
            'website', 'directions' => $this->safeWebUrl($value),
        };
    }

    private function safeWebUrl(string $url): string
    {
        $url = str_starts_with($url, 'http://') || str_starts_with($url, 'https://') ? $url : 'https://'.$url;

        if (! filter_var($url, FILTER_VALIDATE_URL) || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw new NotFoundHttpException;
        }

        return $url;
    }

    private function escapeVcard(string $value): string
    {
        return str_replace(['\\', ';', ',', "\r", "\n"], ['\\\\', '\\;', '\\,', '', '\\n'], $value);
    }

    private function record(Request $request, Profile $profile, string $type, array $metadata = []): void
    {
        ProfileEvent::create([
            'organization_id' => $profile->organization_id,
            'profile_id' => $profile->id,
            'event_type' => $type,
            'visitor_hash' => hash_hmac('sha256', (string) $request->ip(), config('app.key')),
            'session_hash' => hash_hmac('sha256', (string) $request->session()->getId(), config('app.key')),
            'metadata' => $metadata ?: null,
            'occurred_at' => now(),
        ]);
    }
}
