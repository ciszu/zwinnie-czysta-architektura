<?php

namespace Mozartify\Subscription\UseCases;

use Mozartify\Subscription\SubscriptionDomain;
use Mozartify\Subscription\SubscriptionDomainException;

class BuyPackageUseCase extends SubscriptionUseCase
{
    public function __invoke(int $subscriptionId, string $newPackageType)
    {
        $activePackage = $this->repository->getActivePackage($subscriptionId);
        $activePackageIsDemo = $activePackage['type'] === SubscriptionDomain::PackageDemo;
        $activePackageIsPremium = $activePackage['type'] === SubscriptionDomain::PackagePremium;

        if ($activePackageIsDemo && $newPackageType === SubscriptionDomain::PackageDemo) {
            throw new SubscriptionDomainException('Cannot buy another demo');
        }
        if (!$activePackageIsDemo && $newPackageType === SubscriptionDomain::PackageDemo) {
            throw new SubscriptionDomainException('Cannot downgrade to demo');
        }
        if ($activePackageIsPremium && $newPackageType === SubscriptionDomain::PackageStandard) {
            throw new SubscriptionDomainException('Cannot downgrade premium package to standard');
        }

        try {
            $heartbeats = SubscriptionDomain::getHeartbeatsByPackageType($newPackageType);
            $this->repository->addPackage($subscriptionId, $newPackageType, $heartbeats);
        } catch (\Exception $e) {
            throw new SubscriptionDomainException('Creating new subscription failed');
        }
    }
}