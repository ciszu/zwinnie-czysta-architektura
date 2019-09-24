<?php

namespace Mozartify\Subscription;

class Domain
{
    const PackageDemo = 'demo';
    const PackageStandard = 'standard';
    const PackagePremium = 'premium';

    /**
     * @var FileStorage
     */
    private $storage;
    /**
     * @var Pardot
     */
    private $ecommerce;

    public function __construct(FileStorage $storage, Pardot $ecommerce)
    {
        $this->storage = $storage;
        $this->ecommerce = $ecommerce;
    }

    public function subscribe($tenantName, $packageType)
    {
        try {
            $heartbeats = self::getHeartbeatsByPackageType($packageType);

            $tenantId = $this->storage->createNewTenant($tenantName);
            $subscriptionId = $this->storage->createNewSubscription($tenantId);
            $this->storage->addPackage($subscriptionId, $packageType, $heartbeats);

            $this->ecommerce->createNewContact($tenantId, $packageType);

        } catch (\Exception $e) {
            throw new DomainException('Creating new tenant failed', $e);
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
        $activePackage = $this->storage->getActivePackage($subscriptionId);
        $activePackageIsDemo = $activePackage['type'] === self::PackageDemo;
        $activePackageIsPremium = $activePackage['type'] === self::PackagePremium;

        if ($activePackageIsDemo && $newPackageType === self::PackageDemo) {
            throw new DomainException('Cannot buy another demo');
        }
        if (!$activePackageIsDemo && $newPackageType === self::PackageDemo) {
            throw new DomainException('Cannot downgrade to demo');
        }
        if ($activePackageIsPremium && $newPackageType === self::PackageStandard) {
            throw new DomainException('Cannot downgrade premium package to standard');
        }

        try {
            $heartbeats = self::getHeartbeatsByPackageType($newPackageType);
            $this->storage->addPackage($subscriptionId, $newPackageType, $heartbeats);
        } catch (\Exception $e) {
            throw new DomainException('Creating new subscription failed');
        }
    }

    public function buyHeartbeats(int $subscriptionId, int $heartbeats)
    {
        $activePackage = $this->storage->getActivePackage($subscriptionId);

        if (!$activePackage) {
            throw new DomainException('Inactive subscription');
        }
        if ($activePackage['type'] === self::PackageDemo) {
            throw new DomainException('Extra heartbeats now allowed on demo');
        }

        try {
            $this->storage->addExtraHeartbeats($subscriptionId, $heartbeats);
        } catch (\Exception $e) {
            throw new DomainException('Buying extra heartbeats failed');
        }

    }

    public function getActivePackage(int $subscriptionId)
    {
        return $this->storage->getActivePackage($subscriptionId);
    }

    public function prepareCommercialOffer(?int $subscriptionId)
    {
        $offer = [
            'extraHeartbeats' => false,
            'availablePackages' => []
        ];
        $allPackageTypes = [self::PackageDemo, self::PackageStandard, self::PackagePremium];

        if ($subscriptionId) {
            $activePackage = $this->getActivePackage($subscriptionId);
            $activePackageIsDemo = $activePackage['type'] === self::PackageDemo;
            $activePackageIsPremium = $activePackage['type'] === self::PackagePremium;
            $notAvailablePackageTypes = [self::PackageDemo];
            !$activePackageIsPremium ?: $notAvailablePackageTypes[] = self::PackageStandard;

            $availablePackageTypes = array_diff($allPackageTypes, $notAvailablePackageTypes);
            $offer['extraHeartbeats'] = !$activePackageIsDemo;
        } else {
            $availablePackageTypes = $allPackageTypes;
        }
        foreach ($availablePackageTypes as $packageType) {
            $offer['availablePackages'][] = [
                'type' => $packageType,
                'heartbeats' => self::getHeartbeatsByPackageType($packageType)
            ];
        }
        return $offer;
    }

}
