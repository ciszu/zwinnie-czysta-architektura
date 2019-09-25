<?php

namespace Mozartify\Subscription;

interface EcommerceAdapter
{
    public function createNewContact(int $tenantId, string $packageType);
}