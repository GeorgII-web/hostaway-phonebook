<?php

namespace App\Rules;

use App\Connectors\HostawayConnector;
use Illuminate\Contracts\Validation\Rule;
use Psr\SimpleCache\InvalidArgumentException;

class HostawayCountryCodeRule implements Rule
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
     * Check if $value in country codes list of the Hostaway API.
     *
     * @param string $attribute Attribute name
     * @param mixed  $value     Value to check
     * @return bool True if valid country code
     */
    public function passes($attribute, $value): bool
    {
        $countryCodeList = $this->hostawayConnector->getCountryCodes();

        if (in_array($value, $countryCodeList, true)) {
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
