<?php

namespace Mozartify\Subscription\UseCases;

use Mozartify\Subscription\EcommerceAdapter;
use Mozartify\Subscription\SubscriptionRepository;

abstract class SubscriptionUseCase
{
    /**
     * @var SubscriptionRepository
     */
    protected $repository;
    /**
     * @var EcommerceAdapter
     */
    protected $ecommerce;

    public function __construct(SubscriptionRepository $repository, EcommerceAdapter $ecommerce)
    {
        $this->repository = $repository;
        $this->ecommerce = $ecommerce;
    }
}