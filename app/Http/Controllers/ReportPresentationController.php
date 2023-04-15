<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class ReportPresentationController extends Controller
{
    public function upload(Request $request): bool|string|null
    {
        if ($request->hasFile('presentation')) {
            return $request->file('presentation')->store(
                'presentations',
                'public'
            );
        }

        return null;
    }

    public function remove(Request $request)
    {
        $report = Report::getReportByConferenceAndCreator(
            $request->conferenceId,
            $request->creatorId
        );
        if ($report?->presentation) {
            $path = storage_path('app/public/'.$report->presentation);
            unlink($path);
        }
    }
}
