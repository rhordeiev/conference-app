<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DurationIsLessThanHourRule implements Rule
{
    protected ?string $startTime;

    protected ?string $endTime;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($startTime, $endTime)
    {
        $this->startTime = $startTime;
        $this->endTime = $endTime;
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
        $startTime = strtotime($this->startTime);
        $endTime = strtotime($this->endTime);
        $duration = ($endTime - $startTime) / 60;
        if ($duration > 60) {
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
        return 'Report duration should be less than or equal to 60 minutes.';
    }
}
