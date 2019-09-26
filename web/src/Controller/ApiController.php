<?php

namespace Web\Controller;

use Mozartify\Marketplace\MarketplaceDomain;
use Mozartify\Subscription\SubscriptionDomain;
use Mozartify\Subscription\SubscriptionDomainException;
use Mozartify\Subscription\FileSystemSubscriptionRepository;
use Mozartify\Subscription\PardotEcommerceAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

class ApiController
{
    /**
     * @var SubscriptionDomain
     */
    private $subscriptionDomain;

    /**
     * @var MarketplaceDomain
     */
    private $marketplaceDomain;

    public function __construct()
    {
        $this->subscriptionDomain = new SubscriptionDomain(
            new FileSystemSubscriptionRepository('../../var/zca3.json'),
            new PardotEcommerceAdapter()
        );
        $this->marketplaceDomain = new MarketplaceDomain($this->subscriptionDomain);
    }

    /**
     * @SWG\Response(
     *     response=200,
     *     description=""
     * )
     * @SWG\Tag(name="subscription")
     */
    public function subscribe(string $tenantName, string $packageType)
    {
        try {
            $subscriptionId = $this->subscriptionDomain->subscribe($tenantName, $packageType);
        } catch (SubscriptionDomainException $e) {
            return $this->decorateDomainException($e);
        }
        return new JsonResponse([
            'subscriptionId' => $subscriptionId
        ], 201);
    }

    /**
     * @SWG\Response(
     *     response=200,
     *     description=""
     * )
     * @SWG\Tag(name="subscription")
     */
    public function buyPackage(int $subscriptionId, string $newPackageType)
    {
        try {
            $this->subscriptionDomain->buyPackage($subscriptionId, $newPackageType);
        } catch (SubscriptionDomainException $e) {
            return $this->decorateDomainException($e);
        }
        return new JsonResponse([
            'success' => true
        ], 201);
    }

    /**
     * @SWG\Response(
     *     response=200,
     *     description=""
     * )
     * @SWG\Tag(name="subscription")
     */
    public function buyHeartbeats(int $subscriptionId, int $heartbeats)
    {
        try {
            $this->subscriptionDomain->buyHeartbeats($subscriptionId, $heartbeats);
        } catch (SubscriptionDomainException $e) {
            return $this->decorateDomainException($e);
        }
        return new JsonResponse([
            'success' => true
        ], 201);
    }

    /**
     * @SWG\Response(
     *     response=200,
     *     description=""
     * )
     * @SWG\Tag(name="subscription")
     */
    public function getActivePackage(int $subscriptionId)
    {
        $activePackage = $this->subscriptionDomain->getActivePackage($subscriptionId);
        return new JsonResponse([
            'activePackage' => $activePackage
        ]);
    }

    /**
     * @SWG\Response(
     *     response=200,
     *     description=""
     * )
     * @SWG\Tag(name="offer")
     */
    public function prepareOfferForNewTenant()
    {
        return $this->prepareCommercialOffer(null);
    }

    /**
     * @SWG\Response(
     *     response=200,
     *     description=""
     * )
     * @SWG\Tag(name="offer")
     */
    public function prepareOfferForExistingTenant(int $subscriptionId)
    {
        return $this->prepareCommercialOffer($subscriptionId);
    }

    private function prepareCommercialOffer(?int $subscriptionId)
    {
        try {
            $offer = $this->marketplaceDomain->prepareCommercialOffer($subscriptionId);
        } catch (SubscriptionDomainException $e) {
            return $this->decorateDomainException($e);
        }
        return new JsonResponse([
            'offer' => $offer
        ]);
    }

    private function decorateDomainException(SubscriptionDomainException $e)
    {
        if ($e->getPrevious()) {
            throw $e->getPrevious();
        }
        return new JsonResponse([
            'msg' => $e->getMessage()
        ], 406);
    }

}
