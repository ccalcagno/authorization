<?php

namespace Calcagno\Authorization\Services;

final class SessionManager
{
  private bool $started = false;
  private string $namespace;
  private int $timeout;

  public function __construct(?string $appKey = null, int $timeout = 1800)
  {
    $this->namespace = $this->resolveNamespace($appKey);
    $this->timeout = $timeout;
  }

  public function start(): void
  {
    if ($this->started) {
      return;
    }

    if (session_status() === PHP_SESSION_NONE) {
      // Define cookie de sessão único por namespace
      session_name('SESSION_' . substr($this->namespace, 0, 8));
      session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']),
        'samesite' => 'Lax'
      ]);
      session_start();
    }

    $this->started = true;
  }

  public function set(string $key, mixed $value): void
  {
    $this->start();
    $_SESSION[$this->namespace][$key] = $value;
  }

  public function get(string $key, mixed $default = null): mixed
  {
    $this->start();
    return $_SESSION[$this->namespace][$key] ?? $default;
  }

  public function clear(): void
  {
    $this->start();

    if (isset($_SESSION[$this->namespace])) {
      unset($_SESSION[$this->namespace]);
    }

    session_regenerate_id(true);
  }

  public function has(string $key): bool
  {
    $this->start();
    return isset($_SESSION[$this->namespace][$key]);
  }

  public function getTimeout(): int
  {
    return $this->timeout;
  }

  private function resolveNamespace(?string $appKey): string
  {
    if ($appKey) {
      return hash('sha256', $appKey);
    }

    // Prioridade 1: variável de ambiente
    $envKey = getenv('APP_KEY');
    if ($envKey) {
      return hash('sha256', $envKey);
    }

    // Prioridade 2: diretório base do projeto
    return hash('sha256', dirname(__DIR__, 2));
  }
}
