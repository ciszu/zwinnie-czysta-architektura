<?php

namespace Mozartify\Subscription;

use Throwable;

class DomainException extends \Exception
{
    public function __construct($message = "", Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}