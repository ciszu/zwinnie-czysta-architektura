<?php

namespace Mozartify\Subscription;

use Mozartify\Subscription\Drivers\RamStorage;

class RamSubscriptionRepository extends SubscriptionRepository
{
    public function __construct()
    {
        $this->storage = new RamStorage();
    }

}
