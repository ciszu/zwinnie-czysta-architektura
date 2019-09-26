<?php

namespace Mozartify\Marketplace\UseCases;

use Mozartify\Subscription\SubscriptionDomain;

class PrepareCommercialOfferUseCase extends MarketplaceUseCase
{
    public function __invoke(?int $subscriptionId)
    {
        $offer = [
            'extraHeartbeats' => false,
            'availablePackages' => []
        ];
        $allPackageTypes = [
            SubscriptionDomain::PackageDemo,
            SubscriptionDomain::PackageStandard,
            SubscriptionDomain::PackagePremium
        ];

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