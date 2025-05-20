<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Models\PaymentGateway;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use App\Services\Paystack\Plan as PaystackPlan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManagePackagesController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('created_at', 'desc')->get();

        return $this->jsonResponse(HTTP_SUCCESS, 'Plans retrived successfully.', [PackageResource::collection($plans)]);
    }

    public function show($plan)
    {
        $plan = Plan::where('id', $plan)->withTrashed()->firstOrFail();

        return $this->jsonResponse(HTTP_SUCCESS, 'Plan retrived successfully.', [new PackageResource($plan)]);
    }
    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'billing_cycle' => 'required|string|in:hourly,monthly,yearly,weekly,daily,quarterly,biannually',
            'price' => 'required|numeric',
            'discount' => 'required|numeric|max:100',
            'features' => 'required|array',
            'description' => 'required|string|max:255',
        ]);

        $response = (new PaystackPlan())->create($request);

        if (data_get($response, 'data.status' != true)) {
            abort(HTTP_BAD_REQUEST, 'Unable to create plan at the moment. Please try again');
        }

        $gateway = PaymentGateway::where('slug', Plan::PAYSTACK)->first();
        abort_if(!$gateway, HTTP_NOT_FOUND, 'Payment gateway not found.');

        $plan = Plan::create([
            'name' => $request->title,
            'interval' => $request->billing_cycle,
            'amount' => $request->price,
            'discount' => $request->discount,
            'description' => $request->description,
            'total_subscriptions' => 0,
            "currency" => data_get($response, 'data.data.currency'),
            "plan_code" => data_get($response, 'data.data.plan_code'),
            'payment_gateway_id' => $gateway->id,
        ]);

        $this->createPermission($request);

        return $this->jsonResponse(HTTP_CREATED, 'New Plan has been added successfully.', [new PackageResource($plan)]);
    }

    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'title' => 'nullable|string|max:100',
            'billing_cycle' => 'nullable|string|in:hourly,monthly,yearly,weekly,daily,quarterly,biannually',
            'price' => 'nullable|numeric',
            'discount' => 'nullable|numeric|max:100',
            'features' => 'nullable|array',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request, $plan) {
                $data = $plan->update([
                    'name' => $request->title ?? $plan->name,
                    'interval' => $request->billing_cycle ?? $plan->interval,
                    'amount' => $request->price ?? $plan->amount,
                    'discount' => $request->discount ?? $plan->discount,
                    'description' => $request->description ?? $plan->description,
                ]);

                if (!empty($request->features)) {
                    $this->createPermission($request);
                }

                $plan->refresh();
                $response = (new PaystackPlan())->update($plan, $plan->plan_code);

                if (data_get($response, 'data.status' != true)) {
                    throw ('Unable to update package');
                }
            });

            return $this->jsonResponse(HTTP_SUCCESS, 'plan updated successfully.', [new PackageResource($plan)]);
        } catch (Exception $e) {
            Log::error($e);
            abort(HTTP_BAD_REQUEST, 'Unable to update Subscription plan. Please try again');
        }
    }

    public function delete(Plan $plan)
    {
        $plan->delete();

        return $this->jsonResponse(HTTP_SUCCESS, 'plan archived successfully.');
    }

    public function archived()
    {
        $plans = Plan::onlyTrashed()->orderBy('created_at', 'desc')->get();

        return $this->jsonResponse(HTTP_SUCCESS, 'Plans retrived successfully.', [PackageResource::collection($plans)]);
    }

    public function unarchive($plan_uuid)
    {
        $plan = Plan::onlyTrashed()->where('id', $plan_uuid)->first();
        abort_if(!$plan, HTTP_BAD_REQUEST, 'Invalid archived plan');

        $plan->update([
            'deleted_at' => null,
        ]);

        return $this->jsonResponse(HTTP_SUCCESS, 'Plan has been unarchived successfully.', [new PackageResource($plan)]);
    }

    private function createPermission($request)
    {
        $permissions = $request->features;

        // Create permissions
        foreach ($permissions as $permission) {
            $formattedName = strtolower($permission);
            $formattedName = str_replace(' ', '-', $formattedName);

            Permission::firstOrCreate([
                'name' => $formattedName,
            ]);
        }

        $plan = $request->title;
        // Create a role for this plan
        $role = Role::firstOrCreate([
            'name' => $plan,
        ]);

        $role->givePermissionTo($permissions);
    }
}
