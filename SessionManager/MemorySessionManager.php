<?php

declare(strict_types=1);

namespace SessionManager;

class MemorySessionManager implements SessionManagerInterface
{
    private ?array $data = null;

    private bool $started = false;
    private bool $locked = true;

    /**
     * Create a new memory session manager with an optional inital state.
     *
     * @param array $store
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
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
        return $this->data[$key];
    }

    /**
     * @throws SessionException
     */
    public function set(string $key, $value): void
    {
        $this->assertWriteable();
        $this->data[$key] = $value;
    }

    /**
     * @throws SessionException
     */
    public function containsKey(string $key): bool
    {
        $this->assertPresent();
        return array_key_exists($key, $this->data);
    }

    /**
     * @throws SessionException
     */
    public function unset(string $key): void
    {
        $this->assertWriteable();
        unset($this->data[$key]);
    }

    /**
     * @throws SessionException
     */
    public function clear(): void
    {
        $this->assertWriteable();
        $this->data = [];
    }

    public function getId(): string
    {
        return 'memsession';
    }

    /**
     * @throws SessionException
     */
    public function map(string $key, callable $fn): void
    {
        $this->assertWriteable();
        $this->data[$key] = $fn($this->store->get($key));
    }

    public function destroy(string $id): void
    {
    }

    public function __toString()
    {
        return self::class.'['. http_build_query($this->data).']';
    }
}
