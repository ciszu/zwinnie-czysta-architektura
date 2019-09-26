<?php

namespace Mozartify\Marketplace\UseCases;

use Mozartify\Subscription\SubscriptionDomain;

abstract class MarketplaceUseCase
{
    /**
     * @var SubscriptionDomain
     */
    protected $subscriptionDomain;

    public function __construct(SubscriptionDomain $subscriptionDomain)
    {
        $this->subscriptionDomain = $subscriptionDomain;
    }
}