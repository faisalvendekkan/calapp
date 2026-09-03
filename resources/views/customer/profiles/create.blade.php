<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-bold text-slate-950">Create a digital profile</h2></x-slot>
    <div class="py-10"><div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8"><form method="POST" action="{{ route('profiles.store') }}" class="rounded-3xl bg-white p-6 shadow-sm sm:p-10">@csrf @include('customer.profiles.partials.form', ['profile' => null])<button class="mt-8 rounded-xl bg-blue-700 px-6 py-3 font-bold text-white hover:bg-blue-800">Create and continue</button></form></div></div>
</x-app-layout>
