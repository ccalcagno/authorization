<?php

declare(strict_types=1);

namespace Calcagno\Authorization\Services;

use Calcagno\Authorization\Contracts\UserRepositoryInterface;
use Calcagno\Authorization\Entities\AuthUser;
use Calcagno\Authorization\Exceptions\AuthenticationException;
use Calcagno\Authorization\Exceptions\InactiveUserException;
use Calcagno\Authorization\Exceptions\UserNotFoundException;

final class UserManager
{
  private UserRepositoryInterface $repository;
  private UserSession $userSession;

  public function __construct(UserRepositoryInterface $repository)
  {
    $this->repository = $repository;
    $this->userSession = new UserSession;
  }

  public function login(string $username, string $password): void
  {
    $user = $this->repository->findByUsername($username);

    if ($user == null) {
      throw new UserNotFoundException("Usuário ou senha inválidos.");
    }

    if (!$this->verify($password, $user->getPasswordHash())) {
      throw new AuthenticationException("Usuário ou senha inválidos.");
    }

    if (!$user->isActive()) {
      throw new InactiveUserException("Usuário inativo.");
    }

    if ($this->needsRehash($user->getPasswordHash())) {
      $newHash = $this->hash($password);
      $user->setPassword($newHash);
      $this->repository->update($user);
    }

    $this->userSession->set([
      'id' => $user->getId(),
      'name' => $user->getName(),
      'username' => $user->getUsername()
    ]);
  }

  public function logout(): void
  {
    $this->userSession->clear();
  }

  public function register(int $id, string $name, string $username, string $password): AuthUser
  {
    if ($this->repository->findByUsername($username)) {
      throw new \DomainException("Usuário já existe com esse e-mail.");
    }

    $passowrdHash = $this->hash($password);
    $user = new AuthUser($id, $name, $username, $passowrdHash, true);
    $this->repository->create($user);

    return $user;
  }

  public function updatePassword(int $id, string $newPassword): void
  {
    $user = $this->repository->findById($id);
    if (!$user) {
      throw new \DomainException("Usuário não encontrado.");
    }

    $passowrdHash = $this->hash($newPassword);
    $user->setPassword($passowrdHash);
    $this->repository->update($user);
  }

  private function hash(string $value): string
  {
    $hash = password_hash($value, PASSWORD_DEFAULT);

    if ($hash === false) {
      throw new \UnexpectedValueException('Falha ao gerar hash de senha.');
    }

    return $hash;
  }

  private function verify(string $value, string $hash): bool
  {
    return password_verify($value, $hash);
  }

  private function needsRehash(string $hash): bool
  {
    return password_needs_rehash($hash, PASSWORD_DEFAULT);
  }
}
