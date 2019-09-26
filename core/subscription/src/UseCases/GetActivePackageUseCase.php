<?php

namespace Mozartify\Subscription\UseCases;

use Mozartify\Subscription\SubscriptionDomain;
use Mozartify\Subscription\SubscriptionDomainException;

class GetActivePackageUseCase extends SubscriptionUseCase
{
    public function __invoke(int $subscriptionId)
    {
        return $this->repository->getActivePackage($subscriptionId);
    }
}