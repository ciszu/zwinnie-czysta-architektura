<?php

namespace Mozartify\Subscription;

class FileStorage
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var array
     */
    private $data;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        if (!file_exists($this->filename)) {
            touch($this->filename);
        }
        $rawData = file_get_contents($this->filename) ?: '{}';
        $this->data = json_decode($rawData, true);
    }

    private function flush()
    {
        file_put_contents($this->filename, json_encode($this->data));
    }

    public function __destruct()
    {
        $this->flush();
    }

    private function insert(string $model, $data)
    {
        if (!array_key_exists($model, $this->data)) {
            $this->data[$model] = [];
            $nextId = 1;
        } else {
            $nextId = max(array_keys($this->data[$model])) + 1;
        }
        $this->data[$model][$nextId] = $data;
        $this->flush();
        return $nextId;
    }

    public function update(string $model, int $id, $data)
    {
        $this->data[$model][$id] = $data;
        $this->flush();
    }

    public function delete(string $model, int $id)
    {
        unset($this->data[$model][$id]);
        $this->flush();
    }

    public function truncate()
    {
        $this->data = [];
        $this->flush();
    }

    public function select(string $model, int $id)
    {
        return $this->data[$model][$id];
    }

    public function selectAll($model)
    {
        return $this->data[$model];
    }

    public function createNewTenant(string $tenantName)
    {
        $data = [ 'name' => $tenantName ];
        return $this->insert('tenant', $data);
    }

    public function createNewSubscription(int $tenantId)
    {
        $data = [
            'tenantId' => $tenantId,
            'createdAt' => date('U')
        ];
        return $this->insert('subscription', $data);
    }

    public function addPackage(int $subscriptionId, string $packageType, int $heartbeats)
    {
        $packageData = [
            'subscriptionId' => $subscriptionId,
            'type' => $packageType,
            'heartbeats' => $heartbeats
        ];
        return $this->insert('package', $packageData);
    }

    public function addExtraHeartbeats(int $subscriptionId, int $heartbeats)
    {
        $activePackageIdx = $this->findActivePackageIdx($subscriptionId);
        $activePackage = $this->select('package', $activePackageIdx);
        $activePackage['heartbeats']+= $heartbeats;
        $this->update('package', $activePackageIdx, $activePackage);
    }

    public function getActivePackage(int $subscriptionId)
    {
        $activePackageIdx = $this->findActivePackageIdx($subscriptionId);
        return $this->select('package', $activePackageIdx);
    }

    private function findActivePackageIdx(int $subscriptionId)
    {
        $packages = $this->selectAll('package');
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
