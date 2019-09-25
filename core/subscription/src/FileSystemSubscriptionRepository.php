<?php

namespace Mozartify\Subscription;

use Mozartify\Subscription\Drivers\FileStorage;

class FileSystemSubscriptionRepository extends SubscriptionRepository
{
    public function __construct(string $filename)
    {
        $this->storage = new FileStorage($filename);
    }

}
