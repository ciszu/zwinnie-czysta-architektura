<?php

namespace Mozartify\Marketplace;

use Mozartify\Subscription\SubscriptionDomain;

class MarketplaceDomain
{
    /**
     * @var SubscriptionDomain
     */
    private $subscriptionDomain;

    public function __construct(SubscriptionDomain $subscriptionDomain)
    {
        $this->subscriptionDomain = $subscriptionDomain;
    }

    public function prepareCommercialOffer(?int $subscriptionId)
    {
        $offer = [
            'extraHeartbeats' => false,
            'availablePackages' => []
        ];
        $allPackageTypes = [SubscriptionDomain::PackageDemo, SubscriptionDomain::PackageStandard, SubscriptionDomain::PackagePremium];

        if ($subscriptionId) {
            $activePackage = $this->subscriptionDomain->getActivePackage($subscriptionId);
            $activePackageIsDemo = $activePackage['type'] === SubscriptionDomain::PackageDemo;
            $activePackageIsPremium = $activePackage['type'] === SubscriptionDomain::PackagePremium;
            $notAvailablePackageTypes = [SubscriptionDomain::PackageDemo];
            !$activePackageIsPremium ?: $notAvailablePackageTypes[] = SubscriptionDomain::PackageStandard;

            $availablePackageTypes = array_diff($allPackageTypes, $notAvailablePackageTypes);
            $offer['extraHeartbeats'] = !$activePackageIsDemo;
        } else {
            $availablePackageTypes = $allPackageTypes;
        }
        foreach ($availablePackageTypes as $packageType) {
            $offer['availablePackages'][] = [
                'type' => $packageType,
                'heartbeats' => SubscriptionDomain::getHeartbeatsByPackageType($packageType)
            ];
        }
        return $offer;
    }

}
