<?php

declare(strict_types=1);

namespace Indibit\SessionManager;

interface SessionManagerInterface
{
    public function createOrResume(bool $immediateUnlock = false): void;

    public function isWriteable(): bool;

    public function isPresent(): bool;

    /**
     * @return string ID der Sitzung
     */
    public function getId(): string;

    /**
     * @param string $key
     * @return mixed der Wert zum Schlüssel
     */
    public function get(string $key): mixed;

    /**
     * Wert setzen
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value): void;

    /**
     * Funktion auf den Wert zum Schlüssel anwenden und Ergebnis wieder in der Sitzung speichern
     * @param string $key
     * @param callable $fn
     */
    public function map(string $key, callable $fn): void;

    /**
     * Wert zum Schlüssel löschen
     * @param string $key
     */
    public function unset(string $key): void;

    /**
     * @param string $key
     * @return bool ob für den Schlüssel ein Wert gesetzt ist
     */
    public function containsKey(string $key): bool;

    public function unlock(): void;

    /**
     * Alle Werte aus der Sitzung entfernen
     */
    public function clear(): void;

    public function destroy(string $id): void;
}
