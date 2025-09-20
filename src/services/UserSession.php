<?php

declare(strict_types=1);

namespace Calcagno\Authorization\Services;

use Calcagno\Authorization\Contracts\UserSessionInterface;

final class UserSession
{
  private static bool $started = false;

  private static array $requiredFields = ['id', 'name', 'username'];

  private static function startSession(): void
  {
    if (!self::$started) {
      if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
          'httponly' => true,
          'secure' => isset($_SERVER['HTTPS']),
          'samesite' => 'Lax'
        ]);
        session_start();
      }
      self::$started = true;
    }
  }

  public static function set(array $user): void
  {
    self::startSession();

    $appKey = self::resolveAppKey();

    foreach (self::$requiredFields as $field) {
      if (!isset($user[$field])) {
        throw new \InvalidArgumentException("Campo obrigatório ausente: {$field}");
      }
    }

    $_SESSION['user'] = $user;
    $_SESSION['app_key'] =  $appKey;
    $_SESSION['last_activity'] = time();

    session_regenerate_id(true);
  }

  public static function clear(): void
  {
    self::startSession();

    if (self::$started && session_status() === PHP_SESSION_ACTIVE) {

      $_SESSION = [];
      session_unset();
      session_regenerate_id(true);
      session_destroy();

      self::$started = false;
    }
  }

  public static function get(): ?array
  {
    self::startSession();

    return $_SESSION['user'] ?? null;
  }

  public static function isLogged(): bool
  {
    $user = self::get();

    if (!is_array($user)) {
      return false;
    }

    if (!self::checkRequiredFields($user) || !self::checkLastActivity() || !self::checkAppKey()) {
      self::clear();
      return false;
    }

    $_SESSION['last_activity'] = time();

    return true;
  }

  public static function getId(): int|string|null
  {
    return self::getField('id');
  }

  public static function getName(): ?string
  {
    return self::getField('name');
  }

  public static function getUsername(): ?string
  {
    return self::getField('username');
  }

  private static function getField(string $field): mixed
  {
    $user = self::get();

    if (is_array($user) && array_key_exists($field, $user)) {
      return $user[$field];
    }

    return null;
  }

  private function checkLastActivity(): bool
  {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
      return false;
    }

    return true;
  }

  private function checkAppKey(): bool
  {
    $appKey = self::resolveAppKey();

    if ($appKey && (!isset($_SESSION['app_key']) || $_SESSION['app_key'] !== $appKey)) {
      return false;
    }

    return true;
  }

  private function checkRequiredFields(array $fields): bool
  {
    foreach (self::$requiredFields as $field) {
      if (!isset($fields[$field])) {
        return false;
      }
    }

    return true;
  }

  private static function resolveAppKey(): string
  {
    // Prioridade 1: variável de ambiente
    $envKey = getenv('APP_KEY');
    if ($envKey) {
      return hash('sha256', $envKey);
    }

    // Prioridade 2: diretório base do projeto
    return hash('sha256', dirname(__DIR__, 2));
  }
}
