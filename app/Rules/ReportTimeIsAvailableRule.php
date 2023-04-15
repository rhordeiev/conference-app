<?php

namespace App\Rules;

use App\Models\Report;
use Illuminate\Contracts\Validation\Rule;

class ReportTimeIsAvailableRule implements Rule
{
    protected ?int $conferenceId;

    protected ?int $reportId;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($conferenceId, $reportId)
    {
        $this->conferenceId = $conferenceId;
        $this->reportId = $reportId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        $chosenTime = strtotime($value);
        $reports = Report::getReportsByConference($this->conferenceId);
        foreach ($reports as $report) {
            if ($this->reportId === $report->id) {
                continue;
            }
            $currentStartTime = strtotime($report->start_time);
            $currentEndTime = strtotime($report->end_time);
            if ($chosenTime >= $currentStartTime && $chosenTime <= $currentEndTime) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This time is already taken';
    }
}
