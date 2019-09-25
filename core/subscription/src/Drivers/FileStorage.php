<?php

namespace Mozartify\Subscription\Drivers;

class FileStorage extends Storage
{
    /**
     * @var string
     */
    private $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        if (!file_exists($this->filename)) {
            touch($this->filename);
        }
        $rawData = file_get_contents($this->filename) ?: '{}';
        $this->data = json_decode($rawData, true);
    }

    public function flush()
    {
        file_put_contents($this->filename, json_encode($this->data));
    }

    public function __destruct()
    {
        $this->flush();
    }

    public function insert(string $model, $data)
    {
        $nextId = parent::insert($model, $data);
        $this->flush();
        return $nextId;
    }

    public function update(string $model, int $id, $data)
    {
        parent::update($model, $id, $data);
        $this->flush();
    }

    public function delete(string $model, int $id)
    {
        parent::delete($model, $id);
        $this->flush();
    }

    public function truncate()
    {
        parent::truncate();
        $this->flush();
    }

    public function select(string $model, int $id)
    {
        return parent::select($model, $id);
    }

    public function selectAll(string $model)
    {
        return parent::selectAll($model);
    }
}