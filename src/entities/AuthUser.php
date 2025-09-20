<?php

declare(strict_types=1);

namespace Calcagno\Authorization\Entities;

final class AuthUser
{
  private int $id;
  private string $name;
  private string $username;
  private string $passwordHash;
  private bool $isActive;

  public function __construct(int $id, string $name, string $username, string $passwordHash, bool $isActive)
  {
    $this->id = $id;
    $this->name = $name;
    $this->username = $username;
    $this->passwordHash = $passwordHash;
    $this->isActive = $isActive;
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getUsername(): string
  {
    return $this->username;
  }

  public function getPasswordHash(): string
  {
    return $this->passwordHash;
  }

  public function isActive(): bool
  {
    return $this->isActive;
  }

  public function setPassword(string $hash): void
  {
    $this->passwordHash = $hash;
  }

  public static function fromArray(array $data): self
  {
    return new self(
      (int) ($data['id'] ?? 0),
      (string) ($data['name'] ?? ''),
      (string) ($data['username'] ?? ''),
      (string) ($data['password'] ?? ''),
      (bool) ($data['is_active'] ?? true)
    );
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'username' => $this->username,
      'password' => $this->passwordHash,
      'is_active' => $this->isActive,
    ];
  }
}
