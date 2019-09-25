<?php

namespace Mozartify\Subscription\Drivers;

abstract class Storage
{
    /**
     * @var array
     */
    protected $data = [];

    public function insert(string $model, $data)
    {
        if (!array_key_exists($model, $this->data)) {
            $this->data[$model] = [];
            $nextId = 1;
        } else {
            $nextId = max(array_keys($this->data[$model])) + 1;
        }
        $this->data[$model][$nextId] = $data;
        return $nextId;
    }

    public function update(string $model, int $id, $data)
    {
        $this->data[$model][$id] = $data;
    }

    public function delete(string $model, int $id)
    {
        unset($this->data[$model][$id]);
    }

    public function truncate()
    {
        $this->data = [];
    }

    public function select(string $model, int $id)
    {
        return $this->data[$model][$id];
    }

    public function selectAll(string $model)
    {
        return $this->data[$model];
    }
}