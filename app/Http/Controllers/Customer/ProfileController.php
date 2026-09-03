<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Profile::class);
        $organizationIds = request()->user()->organizations()->wherePivot('status', 'active')->pluck('organizations.id');
        $profiles = Profile::query()->whereIn('organization_id', $organizationIds)->latest()->paginate(12);

        return view('customer.profiles.index', compact('profiles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Profile::class);
        $organizations = request()->user()->organizations()->wherePivot('status', 'active')->orderBy('name')->get();

        return view('customer.profiles.create', compact('organizations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProfileRequest $request): RedirectResponse
    {
        $profile = Profile::create($request->validated() + ['owner_id' => $request->user()->id]);

        return redirect()->route('profiles.edit', $profile)->with('status', 'Profile created. Continue building your profile.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Profile $profile): RedirectResponse
    {
        $this->authorize('view', $profile);

        return redirect()->route('profiles.edit', $profile);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Profile $profile): View
    {
        $this->authorize('update', $profile);

        return view('customer.profiles.edit', compact('profile'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProfileRequest $request, Profile $profile): RedirectResponse
    {
        $data = $request->validated();
        if ($data['status'] === 'published' && $profile->published_at === null) {
            $data['published_at'] = now();
        }
        $profile->update($data);

        return back()->with('status', 'Profile saved.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile): RedirectResponse
    {
        $this->authorize('delete', $profile);
        $profile->delete();

        return redirect()->route('profiles.index')->with('status', 'Profile moved to trash.');
    }
}
