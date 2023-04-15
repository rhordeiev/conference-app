<?php

namespace App\Helpers;

use App\Http\Controllers\ZoomController;
use App\Models\Report;
use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Collection;

class ReportHelper
{
    public static function addFavoriteFieldToReport(int $userId, Report $report): bool
    {
        return (bool) $report->isFavorite($userId);
    }

    public static function addFieldToReport(Collection $reports, User $listener, string $column): void
    {
        foreach ($reports as $report) {
            $listener->hasListenerReport($report['id']) ?
                $report[$column] = true
                : $report[$column] = false;
        }
    }

    /**
     * @throws GuzzleException
     */
    public static function addUrlToOnlineReports($report, $user): void
    {
        if ($report->meeting_id) {
            $meetingData = ZoomController::getMeeting(
                $report->meeting_id
            );
            if ($user['type'] === 'member') {
                $report['start_url'] = $meetingData->start_url;
            } else {
                if ($user['type'] === 'listener' && $report['joined']) {
                    $report['join_url'] = $meetingData->join_url;
                }
            }
        }
    }
}
