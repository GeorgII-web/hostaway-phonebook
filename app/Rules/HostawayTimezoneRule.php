<?php

namespace App\Rules;

use App\Connectors\HostawayConnector;
use Illuminate\Contracts\Validation\Rule;

class HostawayTimezoneRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @param HostawayConnector|null $hostawayConnector
     */
    public function __construct(protected ?HostawayConnector $hostawayConnector = null)
    {
        if (!$hostawayConnector) {
            $this->hostawayConnector = (new HostawayConnector);
        }
    }

    /**
     * Determine if the validation rule passes.
     * Check if $value in timezone list of the Hostaway API.
     *
     * @param string $attribute Attribute name
     * @param mixed  $value     Value to check
     * @return bool True if valid timezone
     */
    public function passes($attribute, $value): bool
    {
        $timeZoneList = $this->hostawayConnector->getTimeZones();

        if (in_array($value, $timeZoneList, true)) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be Hostaway format.';
    }
}
