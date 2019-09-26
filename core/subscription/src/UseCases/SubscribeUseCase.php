<?php

namespace Mozartify\Subscription\UseCases;

use Mozartify\Subscription\SubscriptionDomain;
use Mozartify\Subscription\SubscriptionDomainException;

class SubscribeUseCase extends SubscriptionUseCase
{
    public function __invoke(string $tenantName, string $packageType)
    {
        try {
            $heartbeats = SubscriptionDomain::getHeartbeatsByPackageType($packageType);

            $tenantId = $this->repository->createNewTenant($tenantName);
            $subscriptionId = $this->repository->createNewSubscription($tenantId);
            $this->repository->addPackage($subscriptionId, $packageType, $heartbeats);

            $this->ecommerce->createNewContact($tenantId, $packageType);

        } catch (\Exception $e) {
            throw new SubscriptionDomainException('Creating new tenant failed', $e);
        }
        return $subscriptionId;
    }
}