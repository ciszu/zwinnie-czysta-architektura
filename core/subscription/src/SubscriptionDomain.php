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

    public function __call($name, $arguments)
    {
        $classname = 'Mozartify\\Subscription\\UseCases\\' . ucfirst($name) . 'UseCase';
        $obj = new $classname($this->repository, $this->ecommerce);
        return call_user_func_array([$obj, '__invoke'], $arguments);
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

}
