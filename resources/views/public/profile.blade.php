<!DOCTYPE html>
<html lang="{{ $profile->preferred_language }}" dir="{{ $profile->preferred_language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $profile->name }} | BATAQAH</title>
    <meta name="description" content="{{ $profile->tagline ?: Str::limit($profile->about, 150) }}">
    <meta property="og:title" content="{{ $profile->name }}">
    <meta property="og:description" content="{{ $profile->tagline ?: Str::limit($profile->about, 150) }}">
    <meta property="og:type" content="profile">
    <meta property="og:url" content="{{ route('public.profile', $profile->slug) }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-950">
<main class="mx-auto min-h-screen max-w-2xl bg-white shadow-2xl">
    <header class="bg-gradient-to-br from-slate-950 via-blue-950 to-blue-700 px-6 pb-10 pt-16 text-white">
        <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-3xl border-4 border-white/30 bg-cyan-400 text-3xl font-black text-slate-950">
            {{ Str::upper(Str::substr($profile->name, 0, 2)) }}
        </div>
        <div class="mt-6 text-center">
            <h1 class="text-3xl font-black">{{ $profile->name }}</h1>
            <p class="mt-2 text-cyan-200">{{ $profile->designation }}{{ $profile->designation && $profile->company_name ? ' · ' : '' }}{{ $profile->company_name }}</p>
            <p class="mt-3 text-blue-100">{{ $profile->tagline }}</p>
        </div>
    </header>

    <div class="space-y-8 px-6 py-8">
        <section aria-label="Contact actions" class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach ($profile->contacts->where('is_visible', true) as $contact)
                @if (in_array($contact->type, ['mobile', 'telephone', 'whatsapp', 'email', 'website', 'directions'], true))
                    <a href="{{ route('public.contact', [$profile->slug, $contact->type]) }}" class="rounded-2xl bg-blue-50 p-4 text-center font-bold text-blue-800 transition hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-600" rel="nofollow">
                        {{ $contact->label ?: ucfirst($contact->type) }}
                    </a>
                @endif
            @endforeach
            <a href="{{ route('public.vcard', $profile->slug) }}" class="rounded-2xl bg-red-50 p-4 text-center font-bold text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600">Save contact</a>
            <button type="button" id="share-profile" class="rounded-2xl bg-slate-100 p-4 text-center font-bold text-slate-800 transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-600">Share profile</button>
        </section>

        <p id="share-status" class="hidden rounded-xl bg-emerald-50 p-3 text-center text-sm text-emerald-800" role="status"></p>

        @if ($profile->about)
            <section>
                <h2 class="text-xl font-black">About</h2>
                <p class="mt-3 whitespace-pre-line leading-7 text-slate-600">{{ $profile->about }}</p>
            </section>
        @endif

        @if ($profile->services->isNotEmpty())
            <section>
                <h2 class="text-xl font-black">Services</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach ($profile->services as $service)
                        <article class="rounded-2xl border border-slate-200 p-5">
                            <h3 class="font-bold">{{ $service->title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $service->description }}</p>
                            @if ($service->contact_for_price)
                                <p class="mt-3 text-sm font-bold text-blue-700">Contact for price</p>
                            @elseif ($service->price !== null)
                                <p class="mt-3 text-sm font-bold text-blue-700">{{ $service->currency }} {{ number_format($service->price, 2) }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($profile->socialLinks->where('is_visible', true)->isNotEmpty())
            <section>
                <h2 class="text-xl font-black">Connect</h2>
                <div class="mt-4 flex flex-wrap gap-3">
                    @foreach ($profile->socialLinks->where('is_visible', true) as $social)
                        <a href="{{ route('public.social', [$profile->slug, $social->network]) }}" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 hover:border-blue-600 hover:text-blue-700" rel="nofollow noopener">{{ $social->label ?: ucfirst($social->network) }}</a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="rounded-3xl bg-slate-950 p-6 text-center text-white">
            <h2 class="text-xl font-black">Share with QR</h2>
            <img src="{{ route('public.qr', $profile->slug) }}" alt="QR code for {{ $profile->name }}" width="240" height="240" class="mx-auto mt-5 rounded-2xl bg-white p-3">
            <a href="{{ route('public.qr', [$profile->slug, 'download' => 1]) }}" class="mt-5 inline-flex rounded-xl bg-red-600 px-5 py-3 font-bold text-white hover:bg-red-500">Download print-quality SVG</a>
        </section>

        <section>
            <h2 class="text-xl font-black">Send an enquiry</h2>
            @if (session('lead_status'))
                <p class="mt-3 rounded-xl bg-emerald-50 p-4 text-emerald-800">{{ session('lead_status') }}</p>
            @endif
            <form method="POST" action="{{ route('public.leads.store', $profile->slug) }}" class="mt-4 space-y-4">
                @csrf
                <input name="name" placeholder="Your name" required class="w-full rounded-xl border-slate-300">
                <div class="grid gap-4 sm:grid-cols-2">
                    <input name="email" type="email" placeholder="Email" class="w-full rounded-xl border-slate-300">
                    <input name="phone" placeholder="Phone" class="w-full rounded-xl border-slate-300">
                </div>
                <textarea name="message" rows="4" placeholder="How can we help?" required class="w-full rounded-xl border-slate-300"></textarea>
                <label class="flex gap-3 text-sm text-slate-600"><input name="consent" type="checkbox" value="1" required class="mt-1 rounded border-slate-300">I agree to share these details with this business.</label>
                <button class="w-full rounded-xl bg-blue-700 px-6 py-3 font-bold text-white hover:bg-blue-600">Send enquiry</button>
            </form>
        </section>
    </div>
    <footer class="border-t border-slate-200 px-6 py-6 text-center text-sm text-slate-500">Powered by <strong class="text-blue-700">BATAQAH</strong></footer>
</main>
<script>
    document.getElementById('share-profile').addEventListener('click', async () => {
        const data = { title: @js($profile->name), text: @js($profile->tagline), url: @js(route('public.profile', $profile->slug)) };
        const status = document.getElementById('share-status');
        try {
            if (navigator.share) await navigator.share(data);
            else { await navigator.clipboard.writeText(data.url); status.textContent = 'Profile link copied.'; status.classList.remove('hidden'); }
        } catch (error) {
            if (error.name !== 'AbortError') { status.textContent = 'Copy the page address to share this profile.'; status.classList.remove('hidden'); }
        }
    });
</script>
</body>
</html>
