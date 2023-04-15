<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class EndTimeLessThanMaxRule implements Rule
{
    protected ?string $conferenceEndTime;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($conferenceEndTime)
    {
        $this->conferenceEndTime = $conferenceEndTime;
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
        $reportEndTime = strtotime($value);
        $conferenceEndTime = strtotime($this->conferenceEndTime);
        if ($reportEndTime > $conferenceEndTime) {
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
        return "Report end time can't be bigger than conference end time";
    }
}
