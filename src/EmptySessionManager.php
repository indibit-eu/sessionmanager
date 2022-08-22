<?php

declare(strict_types=1);

namespace Indibit\SessionManager;

class EmptySessionManager implements SessionManagerInterface
{
    private bool $started = false;
    private bool $locked = true;

    // Create a new empty session manager
    public function __construct()
    {
    }

    public function getData(): array
    {
        return [];
    }

    public function createOrResume(bool $immediateUnlock = false): void
    {
        if ($this->isPresent()) {
            throw new SessionException('Session wurde bereits erzeugt');
        }
        $this->started = true;
        $this->locked = !$immediateUnlock;
    }

    /**
     * @throws SessionException
     */
    private function assertPresent(): void
    {
        if (!$this->isPresent()) {
            throw new SessionException('Keine Sitzung');
        }
    }

    /**
     * @throws SessionException
     */
    private function assertWriteable(): void
    {
        if (!$this->isWriteable()) {
            throw new SessionException('Die Sitzung ist nicht mehr schreibbar');
        }
    }

    public function isWriteable(): bool
    {
        return $this->isPresent() && $this->locked === true;
    }

    public function isPresent(): bool
    {
        return $this->started;
    }

    /**
     * @throws SessionException
     */
    public function unlock(): void
    {
        $this->assertPresent();
        $this->locked = false;
    }

    /**
     * @throws SessionException
     */
    public function get(string $key): mixed
    {
        $this->assertPresent();
        return null;
    }

    /**
     * @throws SessionException
     */
    public function set(string $key, $value): void
    {
        $this->assertWriteable();
    }

    /**
     * @throws SessionException
     */
    public function containsKey(string $key): bool
    {
        $this->assertPresent();
        return false;
    }

    /**
     * @throws SessionException
     */
    public function unset(string $key): void
    {
        $this->assertWriteable();
    }

    /**
     * @throws SessionException
     */
    public function clear(): void
    {
        $this->assertWriteable();
    }

    public function getId(): string
    {
        return 'emptysession';
    }

    /**
     * @throws SessionException
     */
    public function map(string $key, callable $fn): void
    {
        $this->assertWriteable();
    }

    public function destroy(string $id): void
    {
    }

    public function __toString()
    {
        return self::class.'[]';
    }
}
