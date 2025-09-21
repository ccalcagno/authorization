<?php

declare(strict_types=1);

namespace Calcagno\Authorization\Services;

use Calcagno\Authorization\Contracts\UserSessionInterface;

final class UserSession
{
  private readonly SessionManager $session;
  private static array $requiredFields = ['id', 'name', 'username'];

  public function __construct()
  {
    $this->session = new SessionManager();
  }

  public function set(array $user): void
  {
    foreach (self::$requiredFields as $field) {
      if (!isset($user[$field])) {
        throw new \InvalidArgumentException("Campo obrigatório ausente: {$field}");
      }
    }

    $this->session->set('user', $user);
    $this->session->set('last_activity', time());
  }

  public function clear(): void
  {
    $this->session->clear();
  }

  public function get(): ?array
  {
    return $this->session->get('user');
  }

  public function isLogged(): bool
  {
    $user = $this->get();
    $lastActivity = $this->session->get('last_activity', 0);

    if (!is_array($user)) {
      return false;
    }

    foreach (self::$requiredFields as $field) {
      if (!isset($user[$field])) {
        $this->clear();
        return false;
      }
    }

    if ((time() - $lastActivity) > $this->session->getTimeout()) {
      $this->clear();
      return false;
    }

    $this->session->set('last_activity', time());
    return true;
  }

  public function getId(): int|string|null
  {
    $user = $this->get();
    return $user['id'] ?? null;
  }

  public function getName(): ?string
  {
    $user = $this->get();
    return $user['name'] ?? null;
  }

  public function getUsername(): ?string
  {
    $user = $this->get();
    return $user['username'] ?? null;
  }
}
