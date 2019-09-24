<?php

namespace Web\Controller;

use Mozartify\Subscription\Domain;
use Mozartify\Subscription\DomainException;
use Mozartify\Subscription\FileStorage;
use Mozartify\Subscription\Pardot;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController
{
    /**
     * @var Domain
     */
    private $domain;

    public function __construct()
    {
        $this->domain = new Domain(new FileStorage('../../var/zca1.json'), new Pardot());
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
            $subscriptionId = $this->domain->subscribe($tenantName, $packageType);
        } catch (DomainException $e) {
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
            $this->domain->buyPackage($subscriptionId, $newPackageType);
        } catch (DomainException $e) {
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
            $this->domain->buyHeartbeats($subscriptionId, $heartbeats);
        } catch (DomainException $e) {
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
        try {
            $activePackage = $this->domain->getActivePackage($subscriptionId);
        } catch (\Exception $e) {
            return $this->decorateDomainException($e);
        }
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
            $offer = $this->domain->prepareCommercialOffer($subscriptionId);
        } catch (\Exception $e) {
            return $this->decorateDomainException($e);
        }
        return new JsonResponse([
            'offer' => $offer
        ]);
    }

    private function decorateDomainException(DomainException $e)
    {
        if ($e->getPrevious()) {
            throw $e->getPrevious();
        }
        return new JsonResponse([
            'msg' => $e->getMessage()
        ], 406);
    }

}
