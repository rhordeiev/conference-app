<?php

namespace App\Http\Controllers;

use App\Models\ZoomMeeting;
use App\Services\ZoomService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;

class ZoomController extends Controller
{
    /**
     * @throws GuzzleException
     */
    public static function create(array $report): array
    {
        $zoomService = new ZoomService();
        $meetingInfo = $zoomService->create($report);

        $zoomMeeting = new ZoomMeeting($meetingInfo);
        $zoomMeeting->save();

        Cache::forget('meetings');

        return $meetingInfo;
    }

    /**
     * @throws GuzzleException
     */
    public static function remove(int $meetingId)
    {
        $zoomService = new ZoomService();
        $zoomService->remove($meetingId);

        $zoomMeeting = ZoomMeeting::getMeetingById($meetingId);
        $zoomMeeting->delete();

        Cache::forget('meetings');
    }

    /**
     * @throws GuzzleException
     */
    public static function update(array $report)
    {
        $zoomService = new ZoomService();
        $meetingInfo = $zoomService->update($report);

        $zoomMeeting = ZoomMeeting::getMeetingById($report['meeting_id']);
        $zoomMeeting->update($meetingInfo);

        Cache::forget('meetings');
    }

    /**
     * @throws GuzzleException
     */
    public static function getMeeting(int $meetingId)
    {
        $zoomService = new ZoomService();

        return $zoomService->getMeeting($meetingId);
    }

    /**
     * @throws GuzzleException
     */
    public static function getAllMeetings()
    {
        if (Cache::has('meetings')) {
            return Cache::get('meetings');
        }

        $zoomService = new ZoomService();
        $meetings = $zoomService->getAllMeetings();

        Cache::put('meetings', $meetings);

        return Cache::get('meetings');
    }
}
