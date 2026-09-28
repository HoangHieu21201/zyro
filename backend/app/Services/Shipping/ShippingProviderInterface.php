<?php

namespace App\Services\Shipping;

interface ShippingProviderInterface
{
    /**
     * Check the status of a specific tracking code.
     * 
     * @param string $trackingCode
     * @return array ['status' => string, 'provider_status' => string, 'message' => string|null]
     * 'status' is Zyro's normalized status (e.g. 'completed', 'returned', 'shipping').
     */
    public function checkStatus(string $trackingCode): array;
}
