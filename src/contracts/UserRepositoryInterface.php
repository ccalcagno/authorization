<?php

declare(strict_types=1);

namespace Calcagno\Authorization\Contracts;

use Calcagno\Authorization\Entities\AuthUser;

interface UserRepositoryInterface
{
  public function findById(string $id): ?AuthUser;
  public function findByUsername(string $username): ?AuthUser;
  public function findByEmail(string $email): ?AuthUser;
  public function create(AuthUser $user): void;
  public function update(AuthUser $user): void;
  public function delete(AuthUser $user): void;
}
