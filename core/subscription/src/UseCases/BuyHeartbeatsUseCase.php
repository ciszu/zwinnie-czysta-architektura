<?php

namespace Mozartify\Subscription\UseCases;

use Mozartify\Subscription\SubscriptionDomain;
use Mozartify\Subscription\SubscriptionDomainException;

class BuyHeartbeatsUseCase extends SubscriptionUseCase
{
    public function __invoke(int $subscriptionId, int $heartbeats)
    {
        $activePackage = $this->repository->getActivePackage($subscriptionId);

        if (!$activePackage) {
            throw new SubscriptionDomainException('Inactive subscription');
        }
        if ($activePackage['type'] === SubscriptionDomain::PackageDemo) {
            throw new SubscriptionDomainException('Extra heartbeats now allowed on demo');
        }

        try {
            $this->repository->addExtraHeartbeats($subscriptionId, $heartbeats);
        } catch (\Exception $e) {
            throw new SubscriptionDomainException('Buying extra heartbeats failed');
        }

    }
}