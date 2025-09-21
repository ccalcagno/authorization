<?php

declare(strict_types=1);

namespace Calcagno\Authorization\Exceptions;

use Exception;

final class UserNotFoundException extends Exception
{
  public function __construct(string $message = "")
  {
    parent::__construct($message, 404);
  }
}
