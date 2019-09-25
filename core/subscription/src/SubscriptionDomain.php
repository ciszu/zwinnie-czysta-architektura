<?php

namespace Mozartify\Subscription;

class SubscriptionDomain
{
    const PackageDemo = 'demo';
    const PackageStandard = 'standard';
    const PackagePremium = 'premium';

    /**
     * @var SubscriptionRepository
     */
    private $repository;
    /**
     * @var EcommerceAdapter
     */
    private $ecommerce;

    public function __construct(SubscriptionRepository $repository, EcommerceAdapter $ecommerce)
    {
        $this->repository = $repository;
        $this->ecommerce = $ecommerce;
    }

    public function subscribe($tenantName, $packageType)
    {
        try {
            $heartbeats = self::getHeartbeatsByPackageType($packageType);

            $tenantId = $this->repository->createNewTenant($tenantName);
            $subscriptionId = $this->repository->createNewSubscription($tenantId);
            $this->repository->addPackage($subscriptionId, $packageType, $heartbeats);

            $this->ecommerce->createNewContact($tenantId, $packageType);

        } catch (\Exception $e) {
            throw new SubscriptionDomainException('Creating new tenant failed', $e);
        }
        return $subscriptionId;
    }

    public static function getHeartbeatsByPackageType(string $type)
    {
        $heartbeats = [
            self::PackageDemo => '50',
            self::PackageStandard => '200',
            self::PackagePremium => '500'
        ];
        return $heartbeats[$type];
    }

    public function buyPackage(int $subscriptionId, string $newPackageType)
    {
        $activePackage = $this->repository->getActivePackage($subscriptionId);
        $activePackageIsDemo = $activePackage['type'] === self::PackageDemo;
        $activePackageIsPremium = $activePackage['type'] === self::PackagePremium;

        if ($activePackageIsDemo && $newPackageType === self::PackageDemo) {
            throw new SubscriptionDomainException('Cannot buy another demo');
        }
        if (!$activePackageIsDemo && $newPackageType === self::PackageDemo) {
            throw new SubscriptionDomainException('Cannot downgrade to demo');
        }
        if ($activePackageIsPremium && $newPackageType === self::PackageStandard) {
            throw new SubscriptionDomainException('Cannot downgrade premium package to standard');
        }

        try {
            $heartbeats = self::getHeartbeatsByPackageType($newPackageType);
            $this->repository->addPackage($subscriptionId, $newPackageType, $heartbeats);
        } catch (\Exception $e) {
            throw new SubscriptionDomainException('Creating new subscription failed');
        }
    }

    public function buyHeartbeats(int $subscriptionId, int $heartbeats)
    {
        $activePackage = $this->repository->getActivePackage($subscriptionId);

        if (!$activePackage) {
            throw new SubscriptionDomainException('Inactive subscription');
        }
        if ($activePackage['type'] === self::PackageDemo) {
            throw new SubscriptionDomainException('Extra heartbeats now allowed on demo');
        }

        try {
            $this->repository->addExtraHeartbeats($subscriptionId, $heartbeats);
        } catch (\Exception $e) {
            throw new SubscriptionDomainException('Buying extra heartbeats failed');
        }

    }

    public function getActivePackage(int $subscriptionId)
    {
        return $this->repository->getActivePackage($subscriptionId);
    }

}
