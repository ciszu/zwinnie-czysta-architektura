<?php

namespace Mozartify\Marketplace;

use Mozartify\Subscription\SubscriptionDomain;

class MarketplaceDomain
{
    /**
     * @var SubscriptionDomain
     */
    protected $subscriptionDomain;

    public function __construct(SubscriptionDomain $subscriptionDomain)
    {
        $this->subscriptionDomain = $subscriptionDomain;
    }

    public function __call($name, $arguments)
    {
        $classname = 'Mozartify\\Marketplace\\UseCases\\' . ucfirst($name) . 'UseCase';
        $obj = new $classname($this->subscriptionDomain);
        return call_user_func_array([$obj, '__invoke'], $arguments);
    }
}
