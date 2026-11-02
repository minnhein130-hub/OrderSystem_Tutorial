<?php

namespace App\Exceptions;

use DomainException;

class InvalidOrderStatusTransitionException extends DomainException
{
    public function __construct(string $message = 'Invalid order status transition.')
    {
        parent::__construct($message);
    }
}
