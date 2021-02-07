<?php

namespace App\Services;

use App\Rules\HostawayCountryCodeRule;
use App\Rules\HostawayTimezoneRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class ValidationService
{
    /**
     * Check input data on create or update item.
     *
     * @param Request $request Input data
     * @return void
     * @throws InvalidArgumentException
     */
    public function checkRequestParams(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'min:1', 'max:300'],
            'last_name' => ['min:1', 'max:300'],
            'phone_number' => ['required', 'unique:App\Models\Item,phone_number'],
            'country_code' => [new HostawayCountryCodeRule],
            'timezone_name' => [new HostawayTimezoneRule],
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    /**
     * Is item id numeric.
     *
     * @param string $id Item id
     * @throws InvalidArgumentException
     */
    public function checkItemId(string $id): void
    {
        if (!is_numeric($id)) {
            throw new InvalidArgumentException('Item ID is not numeric.');
        }
    }

    /**
     * Check search text.
     *
     * @param string $text Search text
     * @throws InvalidArgumentException
     */
    public function checkSearchText(string $text): void
    {
        $validator = Validator::make(['search_text' => $text], [
            'search_text' => ['alpha_dash', 'max:300'],
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

}
