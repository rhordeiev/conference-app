<?php

namespace App\Http\Controllers;

use App\Helpers\ConferenceHelper;
use App\Http\Requests\ConferenceRequest;
use App\Models\Conference;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Sanctum\PersonalAccessToken;

class ConferenceController extends Controller
{
    public static function getConference(Request $request, int $id): array
    {
        $conference = Conference::getConferenceById($id);
        if ($request->user && $request->user->type === 'member') {
            $request->user->hasConference($id) ?
                $conference['joined'] = true
                : $conference['joined'] = false;
        } else {
            if ($request->user && $request->user->type === 'announcer') {
                $request->user->hasConference($id) ?
                    $conference['creator'] = true
                    : $conference['creator'] = false;
            }
        }

        return [
            'conference' => $conference,
            'categories' => CategoryController::all(),
            'category' => self::getCategory($conference['id']),
        ];
    }

    public function all(Request $request): Collection|array
    {
        $conferences = Conference::all();
        foreach ($conferences as $conference) {
            $conference['category'] = $conference->conferenceCategory->pluck(
                'id'
            )->first();
            $conference['reportCount'] = $conference->conferenceReports->count(
            );
        }

        $reportCount = $request->query('reportCount');
        if ($reportCount || $reportCount === '0') {
            $conferences = $conferences->filter(
                function ($conference) use ($request) {
                    return $conference['reportCount'] === intval(
                        $request->query(
                            'reportCount'
                        )
                    );
                }
            );
        }
        if ($request->query('fromDate')) {
            $conferences = $conferences->filter(
                function ($conference) use ($request) {
                    $date = date($conference->date);
                    $fromDate = date($request->query('fromDate'));

                    return $date >= $fromDate;
                }
            );
        }
        if ($request->query('toDate')) {
            $conferences = $conferences->filter(
                function ($conference) use ($request) {
                    $date = date($conference->date);
                    $toDate = date($request->query('toDate'));

                    return $date <= $toDate;
                }
            );
        }
        if ($request->query('categories')) {
            $conferences = $conferences->filter(
                function ($conference) use ($request) {
                    return in_array(
                        $conference['category'],
                        $request->query('categories')
                    );
                }
            );
        }
        if ($request->bearerToken()) {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            $user = $token->tokenable;
            if ($conferences->count() !== 0 && $user) {
                if ($user->type === 'member') {
                    ConferenceHelper::addFieldToConference($conferences, $user, 'joined');
                } else {
                    if ($user->type === 'announcer') {
                        ConferenceHelper::addFieldToConference($conferences, $user, 'creator');
                    }
                }
            }
        }

        return $conferences->count() !== 0 ? $conferences : [];
    }

    public static function getCategory(int $id)
    {
        $conference = Conference::getConferenceById($id);

        return $conference->getCategory();
    }

    public function create(ConferenceRequest $request)
    {
        $conference = new Conference($request->validated());
        $conference->save();
        $conference->conferenceUsers()->attach($request->user->id);
    }

    public function edit(ConferenceRequest $request)
    {
        Conference::getConferenceById($request->get('id'))->update(
            $request->validated()
        );
    }

    public function remove(Request $request)
    {
        $conference = Conference::getConferenceById($request->get('id'));
        $conference->delete();
    }

    public function join(Request $request)
    {
        $subscription = $request->user->subscriptions()->active()->get()->last();

        if($subscription && time() >= $subscription->current_period_end) {
            $request->user->join_count = 0;
            $request->user->save();
            $nextBillingTimestamp = $subscription->asStripeSubscription()->current_period_end;
            $nextBillingDateTime = new \DateTime();
            $nextBillingDateTime->setTimestamp($nextBillingTimestamp);
            $subscription->current_period_end = $nextBillingDateTime;
            $subscription->save();
        }

        $userJoinCount = $request->user->join_count;
        $planJoinCount = $request->user->plan_id === null ? 1 : $request->user->plan->join_count;
        if ($userJoinCount < $planJoinCount || $planJoinCount === null) {
            $conference = Conference::getConferenceById($request->get('id'));
            $conference->conferenceUsers()->attach($request->user->id);
            $request->user->join_count++;
            $request->user->save();
        } else {
            return response()->json([
                'message' => "You can't join more conferences. You've exceeded your limit",
            ])->setStatusCode(403);
        }
        return response()->json([
            'message' => "Success",
        ])->setStatusCode(200);
    }

    public function cancel(Request $request)
    {
        $conference = Conference::getConferenceById($request->conferenceId);
        $conference->conferenceUsers()->detach($request->userId);
    }

    public function search(Request $request
    ): \Illuminate\Database\Eloquent\Collection|array {
        return Conference::searchByTitle($request->query('title'));
    }
}
