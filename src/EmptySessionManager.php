<?php

declare(strict_types=1);

namespace Indibit\SessionManager;

class EmptySessionManager implements SessionManagerInterface
{

    // Create a new empty session manager
    public function __construct() { }

    public function createOrResume(bool $immediateUnlock = false): void { }

    /**
     * @throws SessionException
     */
    private function assertPresent(): void
    {
        throw new SessionException('Die öffentliche Sitzung existiert nicht');
    }

    /**
     * @throws SessionException
     */
    private function assertWriteable(): void
    {
        throw new SessionException('Die öffentliche Sitzung ist nicht schreibbar');
    }

    public function isWriteable(): bool
    {
        return false;
    }

    public function isPresent(): bool
    {
        return false;
    }

    /**
     * @throws SessionException
     */
    public function unlock(): void
    {
        $this->assertPresent();
    }

    /**
     * @throws SessionException
     */
    public function get(string $key): mixed
    {
        $this->assertPresent();
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

    public function destroy(string $id): void { }

    public function __toString()
    {
        return self::class . '[]';
    }
}
