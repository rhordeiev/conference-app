<?php

namespace App\Helpers;

use App\Models\Report;

class ZoomMeetingHelper
{
    public static function getTimeDataOfMeeting(Report $report, $conference): array
    {
        $reportDataForMeeting = [];
        $startTime = strtotime($report->start_time);
        $endTime = strtotime($report->end_time);
        $duration = ($endTime - $startTime) / 60;
        $reportDataForMeeting['duration'] = $duration;
        $reportDataForMeeting['start_datetime']
            = "{$conference->date->format('Y-m-d')}T{$report->start_time}";

        return $reportDataForMeeting;
    }
}
