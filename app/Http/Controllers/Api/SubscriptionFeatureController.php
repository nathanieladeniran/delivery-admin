<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeatureResource;
use App\Models\Permission;
use Illuminate\Http\Request;

class SubscriptionFeatureController extends Controller
{
    public function index(Request $request)
    {
        $features = (new Permission);

        if ($request->has('date')) {
            $features->whereDate('created_at', $request->input('date'));
        }

        if ($request->has('name')) {
            $features->where('name', $request->input('name'));
        }

        if ($request->has('paginate') && $request->paginate) {
            $paginate = $request->paginate;
            $paginate = is_numeric($paginate) && $paginate > 0 ? (int)$paginate : 10;

            $paginatedResults = $features->paginate($paginate);
            $data = FeatureResource::collection($paginatedResults)->response()->getData();
        } else {
            $data = FeatureResource::collection($features->get());
        }

        return $this->jsonResponse(HTTP_SUCCESS, 'Subscription features retrieved successfully.', [$data]);
    }

    public function show(Permission $feature)
    {
        return $this->jsonResponse(HTTP_SUCCESS, 'Subscription features retrieved successfully.', [new FeatureResource($feature)]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|max:250',
            'description' => 'required|max:250',
        ]);

        $formattedName = strtolower($request->name);
        $formattedName = str_replace(' ', '-', $formattedName);

        $check = Permission::where('name', $formattedName)->first();

        abort_if($check, HTTP_BAD_REQUEST, 'Feature already exist');

        $feature = Permission::create([
            'name' => $formattedName,
            'description' => $request->description
        ]);

        return $this->jsonResponse(HTTP_SUCCESS, 'Subscription features created successfully.', [new FeatureResource($feature)]);
    }

    public function update(Request $request, Permission $feature)
    {
        $request->validate([
            'name' => 'nullable|max:250',
            'description' => 'nullable|max:250',
        ]);

        $formattedName = null;

        if ($request->name) {
            $check = Permission::where('name', $formattedName)->first();
            abort_if($check, HTTP_BAD_REQUEST, 'Feature already exist. Use a new name');

            $formattedName = strtolower($request->name);
            $formattedName = str_replace(' ', '-', $formattedName);
        }

        $feature->update([
            'name' => $formattedName ?? $feature->name,
            'description' => $request->description ?? $feature->description
        ]);

        return $this->jsonResponse(HTTP_SUCCESS, 'Subscription features updated successfully.', [new FeatureResource($feature)]);
    }

    public function delete(Permission $feature)
    {
        $feature->delete();
        return $this->jsonResponse(HTTP_SUCCESS, 'Subscription features deleted successfully.');
    }
}
