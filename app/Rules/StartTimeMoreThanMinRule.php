<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StartTimeMoreThanMinRule implements Rule
{
    protected ?string $conferenceStartTime;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($conferenceStartTime)
    {
        $this->conferenceStartTime = $conferenceStartTime;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $reportStartTime = strtotime($value);
        $conferenceStartTime = strtotime($this->conferenceStartTime);
        if ($reportStartTime < $conferenceStartTime) {
            return false;
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
        return "Report start time can't be less than conference start time";
    }
}
