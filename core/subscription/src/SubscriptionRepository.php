<?php

namespace Mozartify\Subscription;

abstract class SubscriptionRepository
{
    /**
     * @var Storage
     */
    protected $storage;

    public function createNewTenant(string $tenantName)
    {
        $data = [ 'name' => $tenantName ];
        return $this->storage->insert('tenant', $data);
    }

    public function createNewSubscription(int $tenantId)
    {
        $data = [
            'tenantId' => $tenantId,
            'createdAt' => date('U')
        ];
        return $this->storage->insert('subscription', $data);
    }

    public function addPackage(int $subscriptionId, string $packageType, int $heartbeats)
    {
        $packageData = [
            'subscriptionId' => $subscriptionId,
            'type' => $packageType,
            'heartbeats' => $heartbeats
        ];
        return $this->storage->insert('package', $packageData);
    }

    public function addExtraHeartbeats(int $subscriptionId, int $heartbeats)
    {
        $activePackageIdx = $this->findActivePackageIdx($subscriptionId);
        $activePackage = $this->storage->select('package', $activePackageIdx);
        $activePackage['heartbeats']+= $heartbeats;
        $this->storage->update('package', $activePackageIdx, $activePackage);
    }

    public function getActivePackage(int $subscriptionId)
    {
        $activePackageIdx = $this->findActivePackageIdx($subscriptionId);
        return $this->storage->select('package', $activePackageIdx);
    }

    protected function findActivePackageIdx(int $subscriptionId)
    {
        $packages = $this->storage->selectAll('package');
        $findPackageCallback = function($row) use ($subscriptionId) {
            if ($row['subscriptionId'] === $subscriptionId) {
                return true;
            }
        };
        $res = array_reverse(array_filter($packages, $findPackageCallback), true);
        $idx = array_keys($res)[0];
        return $idx;
    }
}