<?php

namespace App\Connectors;

interface HostawayConnectorInterface
{
    /**
     * @return array
     */
    public function getCountryCodes(): array;

    /**
     * @return array
     */
    public function getTimeZones(): array;
}

