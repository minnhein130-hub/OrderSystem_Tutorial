<?php

namespace App\Exceptions;

use DomainException;

class InvalidOrderItemsException extends DomainException
{
    public function __construct(string $message = 'The selected order items are no longer available. Please review the order and try again.')
    {
        parent::__construct($message);
    }
}
