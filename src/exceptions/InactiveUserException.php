<?php

declare(strict_types=1);

namespace Calcagno\Authorization\Exceptions;

class InactiveUserException extends \RuntimeException
{
  public function __construct(string $message = "")
  {
    parent::__construct($message, 403);
  }
}
