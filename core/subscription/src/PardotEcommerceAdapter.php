<?php

namespace Mozartify\Subscription;

class PardotEcommerceAdapter implements EcommerceAdapter
{
    public function createNewContact(int $tenantId, string $packageType)
    {
        return true;
    }
}