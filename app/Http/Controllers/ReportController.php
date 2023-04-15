<?php

namespace App\Http\Controllers;

use App\Helpers\ReportHelper;
use App\Helpers\ZoomMeetingHelper;
use App\Http\Requests\ReportRequest;
use App\Mail\NewReportMailable;
use App\Mail\ReportDeletedByAdminMailable;
use App\Mail\ReportTimeUpdatedMailable;
use App\Models\Conference;
use App\Models\Report;
use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;

class ReportController extends Controller
{
    /**
     * @throws GuzzleException
     */
    public function getReport(Request $request, int $id): array
    {
        $report = Report::getReportById($id);
        if ($request->bearerToken()) {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            $user = $token->tokenable;
            if ($request->user && $request->user->type === 'listener') {
                $request->user->hasListenerReport($id) ?
                    $report['joined'] = true
                    : $report['joined'] = false;
            }
            $report['favorite'] = ReportHelper::addFavoriteFieldToReport(
                $user['id'],
                $report
            );
            $report->date = Conference::getConferenceById(
                $report->conference_id
            )->date;
            ReportHelper::addUrlToOnlineReports($report, $user);
        }
        $conference = ConferenceController::getConference(
            $request,
            $report['conference_id']
        )['conference'];
        $report['date'] = $conference->date;

        return [
            'report' => $report,
            'conference' => $conference,
            'categories' => CategoryController::all(),
            'category' => $report->reportCategory->first(),
        ];
    }

    /**
     * @throws GuzzleException
     */
    public function all(Request $request): Collection|array
    {
        $reports = Report::all();
        foreach ($reports as $report) {
            $report['category'] = $report->reportCategory->pluck('id')->first();
        }

        if ($request->query('fromTime')) {
            $reports = $reports->filter(function ($report) use ($request) {
                $startTime = strtotime($report->start_time);
                $fromTime = strtotime($request->query('fromTime'));

                return $startTime >= $fromTime;
            });
        }
        if ($request->query('toTime')) {
            $reports = $reports->filter(function ($report) use ($request) {
                $endTime = strtotime($report->end_time);
                $toTime = strtotime($request->query('toTime'));

                return $endTime <= $toTime;
            });
        }
        if ($request->query('duration')) {
            $reports = $reports->filter(function ($report) use ($request) {
                $start = strtotime($report->start_time);
                $end = strtotime($report->end_time);
                $durationInMinutes = ($end - $start) / 60;

                return $durationInMinutes === intval(
                    $request->query('duration')
                );
            });
        }
        if ($request->query('categories')) {
            $reports = $reports->filter(function ($report) use ($request) {
                return in_array(
                    $report['category'],
                    $request->query('categories')
                );
            });
        }
        if ($request->bearerToken()) {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            $user = $token->tokenable;
            foreach ($reports as $report) {
                ReportHelper::addFieldToReport($reports, $user, 'joined');
                $report['favorite'] = ReportHelper::addFavoriteFieldToReport(
                    $user['id'],
                    $report
                );
                $report->date = Conference::getConferenceById(
                    $report->conference_id
                )->date;
                ReportHelper::addUrlToOnlineReports($report, $user);
            }
        }

        return $reports->count() !== 0 ? $reports : [];
    }

    public function getReportsByConference(
        int $id
    ): \Illuminate\Database\Eloquent\Collection|array {
        $reports = Report::getReportsByConference($id);

        return $reports->count() !== 0 ? $reports : [];
    }

    public function create(ReportRequest $request): array
    {
        $reportToCreate = $request->validated();
        $reportToCreate['creator_id'] = $request->user->id;
        $report = new Report($reportToCreate);
        $report->save();

        if ($report->id) {
            $conference = Conference::getConferenceById($report->conference_id);
            $users = $conference->getUsersById($report->conference_id);
            foreach ($users as $user) {
                if ($user->type === 'announcer' || $user->type === 'admin'
                    || $user->id == $request->user->id
                ) {
                    continue;
                }
                Mail::to($user->email)->queue(
                    new NewReportMailable(
                        $conference->title,
                        $conference->id,
                        "{$request->user->firstname} {$request->user->lastname}",
                        $report->topic,
                        $report->id,
                        "{$report->start_time}-{$report->end_time}"
                    )
                );
            }

            if ($request->online) {
                $reportDataForMeeting = [
                    'topic' => $report->topic,
                    'description' => $report->description,
                ];
                $reportDataForMeeting = array_merge(
                    $reportDataForMeeting,
                    ZoomMeetingHelper::getTimeDataOfMeeting($report, $conference)
                );
                $meetingData = ZoomController::create($reportDataForMeeting);
                $report->meeting_id = $meetingData['id'];
                $report->save();

                return ['start_url' => $meetingData['start_url']];
            }
        }

        return [];
    }

    public function remove(Request $request)
    {
        $report = Report::getReportByConferenceAndCreator(
            $request->conferenceId,
            $request->creatorId
        );
        $deleteSuccess = $report->delete();

        if ($report->meeting_id) {
            ZoomController::remove($report->meeting_id);
        }

        if ($deleteSuccess && $request->user->type === 'admin') {
            $creator = User::getUserById($request->creatorId);
            $conference = Conference::getConferenceById($request->conferenceId);
            Mail::to($creator->email)->queue(
                new ReportDeletedByAdminMailable(
                    $conference->title,
                    $conference->id
                )
            );
        }
    }

    /**
     * @throws GuzzleException
     */
    public function edit(ReportRequest $request)
    {
        $report = Report::getReportById($request->id);
        $reportBeforeStartTime = $report->start_time;
        $reportBeforeEndTime = $report->end_time;
        $newReportData = $request->validated();
        $updateSuccess = $report->update(
            $newReportData
        );
        $startTimeDiffers = $reportBeforeStartTime
            !== $newReportData['start_time'];
        $endTimeDiffers = $reportBeforeEndTime
            !== $newReportData['end_time'];
        if ($updateSuccess) {
            $conference = Conference::getConferenceById($report->conference_id);

            if ($startTimeDiffers || $endTimeDiffers) {
                $users = $conference->getUsersById($report->conference_id);
                foreach ($users as $user) {
                    if ($user->type === 'announcer' || $user->type === 'admin'
                        || $user->id == $request->user->id
                    ) {
                        continue;
                    }
                    Mail::to($user->email)->queue(
                        new ReportTimeUpdatedMailable(
                            $conference->title,
                            $conference->id,
                            "{$request->user->firstname} {$request->user->lastname}",
                            $report->topic,
                            $report->id,
                            "{$report->start_time}-{$report->end_time}"
                        )
                    );
                }
            }

            if ($report->meeting_id) {
                $reportDataForMeeting = [
                    'topic' => $report->topic,
                    'description' => $report->description,
                    'meeting_id' => $report->meeting_id,
                ];
                $reportDataForMeeting = array_merge(
                    $reportDataForMeeting,
                    ZoomMeetingHelper::getTimeDataOfMeeting($report, $conference)
                );
                ZoomController::update($reportDataForMeeting);
            }
        }
    }

    public function getCategory(int $id)
    {
        $report = Report::getReportById($id);

        return $report->getCategory();
    }

    public function addToFavorites(Request $request)
    {
        $category = Report::getReportById($request->id);
        $category->favoritesUsers()->attach($request->user->id);
    }

    public function removeFromFavorites(Request $request)
    {
        $category = Report::getReportById($request->id);
        $category->favoritesUsers()->detach($request->user->id);
    }

    public function getFavoritesCount(Request $request): int
    {
        $reports = $this->all($request);
        $count = 0;
        foreach ($reports as $report) {
            if ($report->favorite) {
                $count++;
            }
        }

        return $count;
    }

    public function getFavoritesByUser(Request $request
    ): \Illuminate\Database\Eloquent\Collection|array {
        $reports = $this->all($request);
        $favoriteReports = [];
        foreach ($reports as $report) {
            if ($report->favorite) {
                $favoriteReports[] = $report;
            }
        }

        return count($favoriteReports) !== 0 ? $favoriteReports : [];
    }

    public function search(Request $request
    ): \Illuminate\Database\Eloquent\Collection|array {
        $reports = Report::searchByTopic($request->query('topic'));
        foreach ($reports as $report) {
            $conference = Conference::getConferenceById($report->conference_id);
            $report->date = $conference->date;
        }

        return $reports;
    }

    public function join(Request $request): array
    {
        $report = Report::getReportById($request->get('id'));
        $report->reportListeners()->attach($request->user->id);
        if ($report->meeting_id) {
            $meeting = ZoomController::getMeeting($report->meeting_id);
            $conference = Conference::getConferenceById($report->conference_id);

            return [
                'date' => $conference->date,
                'join_url' => $meeting->join_url,
            ];
        }

        return [];
    }

    public function cancel(Request $request)
    {
        $report = Report::getReportById($request->get('id'));
        $report->reportListeners()->detach($request->user->id);
    }
}
