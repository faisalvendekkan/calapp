<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Profile::class);
        $organizationIds = request()->user()->organizations()->wherePivot('status', 'active')->pluck('organizations.id');

        return ProfileResource::collection(Profile::whereIn('organization_id', $organizationIds)->latest()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProfileRequest $request): ProfileResource
    {
        $profile = Profile::create($request->validated() + ['owner_id' => $request->user()->id]);

        return new ProfileResource($profile);
    }

    /**
     * Display the specified resource.
     */
    public function show(Profile $profile): ProfileResource
    {
        $this->authorize('view', $profile);

        return new ProfileResource($profile->load(['contacts', 'socialLinks', 'services', 'products']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProfileRequest $request, Profile $profile): ProfileResource
    {
        $profile->update($request->validated());

        return new ProfileResource($profile->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile): Response
    {
        $this->authorize('delete', $profile);
        $profile->delete();

        return response()->noContent();
    }
}
