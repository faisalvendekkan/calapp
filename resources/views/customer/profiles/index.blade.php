<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-600">Workspace</p><h2 class="text-2xl font-bold text-slate-950">Digital profiles</h2></div>
            <a href="{{ route('profiles.create') }}" class="rounded-xl bg-blue-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-700/20 hover:bg-blue-800">Create profile</a>
        </div>
    </x-slot>
    <div class="py-10"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @if (session('status'))<div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">{{ session('status') }}</div>@endif
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($profiles as $profile)
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-widest text-blue-700">{{ str_replace('_', ' ', $profile->type) }}</p><h3 class="mt-2 text-xl font-bold text-slate-950">{{ $profile->name }}</h3><p class="mt-1 text-sm text-slate-500">{{ $profile->company_name ?: 'Independent profile' }}</p></div><span class="rounded-full px-3 py-1 text-xs font-bold {{ $profile->status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ str_replace('_', ' ', $profile->status) }}</span></div>
                    <div class="mt-6 flex gap-3"><a href="{{ route('profiles.edit', $profile) }}" class="rounded-lg bg-slate-950 px-4 py-2 text-sm font-semibold text-white">Edit</a>@if ($profile->status === 'published')<a href="{{ route('public.profile', $profile->slug) }}" target="_blank" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">View live</a>@endif</div>
                </article>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center"><h3 class="text-xl font-bold text-slate-950">Create your first digital profile</h3><p class="mx-auto mt-2 max-w-xl text-slate-600">Share your business, services and contact details through one polished mobile-first page.</p></div>
            @endforelse
        </div><div class="mt-8">{{ $profiles->links() }}</div>
    </div></div>
</x-app-layout>
