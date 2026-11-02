<?php

namespace App\Exceptions;

use App\Enums\OrderStatus;
use DomainException;

class OrderNotEditableException extends DomainException
{
    public function __construct(OrderStatus $status)
    {
        $message = match ($status) {
            OrderStatus::Completed => 'Order cannot be edited because it is already completed.',
            OrderStatus::Cancelled => 'Order cannot be edited because it is already cancelled.',
            OrderStatus::Pending => 'Order cannot be edited in its current state.',
        };

        parent::__construct($message);
    }
}
