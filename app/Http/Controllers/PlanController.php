<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function all(): Collection
    {
        return Plan::all();
    }

    public function getPlan($id)
    {
        return Plan::all()->find($id);
    }

    public function createSetupIntent(Request $request)
    {
        return $request->user->createSetupIntent();
    }

    public function subscribe(Request $request)
    {
        $plan = Plan::all()->find($request->planId);
        $planName = $plan->join_count ? "{$plan->join_count}join" : 'unlimited';

        if ($request->user->plan) {
            $subscription = $request->user->subscriptions()->active()->get()->last();
            $request->user->subscription($subscription->name)->swapAndInvoice($plan->stripe_plan);
        } else {
            $request->user->newSubscription($planName, $plan->stripe_plan)->create($request->paymentMethod);
            $subscription = $request->user->subscriptions()->active()->get()->last();
        }

        $nextBillingTimestamp = $subscription->asStripeSubscription()->current_period_end;
        $nextBillingDateTime = new \DateTime();
        $nextBillingDateTime->setTimestamp($nextBillingTimestamp);
        $subscription->current_period_end = $nextBillingDateTime;
        $subscription->save();

        $request->user->plan_id = $plan->id;
        $request->user->save();
    }

    public function cancel(Request $request)
    {
        $subscriptions = $request->user->subscriptions()->active()->get();
        $subscriptions->map(function ($subscription) {
            $subscription->cancel();
        });
        $request->user->plan_id = null;
        $request->user->save();
    }
}
