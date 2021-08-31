<?php

declare(strict_types=1);

namespace SessionManager;

/**
 * Verwaltung von nativen PHP-Sitzungen mit session.save_handler=files
 */
class PhpFsSessionManager implements SessionManagerInterface
{
    private bool $createdOrResumed = false;

    /**
     * @return bool ob Sessions in PHP überhaupt aktiviert sind
     */
    private function isSupported(): bool
    {
        return session_status() !== PHP_SESSION_DISABLED;
    }

    /**
     * @return bool ob eine Sitzung gestartet oder fortgeführt wurde und diese schreibbar ist
     */
    public function isWriteable(): bool
    {
        /*
         * session_status() liefert nur PHP_SESSION_ACTIVE wenn session_start() ohne 'read_and_close' aufgerufen wurde.
         * PHP schließt ja mit 'read_and_close' die Session wieder und liefert das dann hier auch.
         */
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * @return bool ob eine Sitzung gestartet oder fortgeführt wurde
     */
    public function isPresent(): bool
    {
        /*
         * Da session_status() nur PHP_SESSION_ACTIVE liefert, wenn session_start() ohne 'read_and_close' aufgerufen
         * wurde, ist das die einzige Möglichkeit, auf eine nicht mehr schreibbare Session prüfen.
         */
        return isset($_SESSION);
    }

    /**
     * @param bool $immediateUnlock ob die Schreibsperre für die Session unmittelbar freigegeben werden soll
     * @return void
     * @throws SessionException
     */
    public function createOrResume(bool $immediateUnlock = false): void
    {
        if ($this->createdOrResumed) {
            throw new SessionException('Sitzung wurde bereits durch '.PhpFsSessionManager::class.' gestartet');
        } else {
            if ($this->isPresent()) {
                throw new SessionException('Sitzung wurde bereits auf anderem weg gestartet');
            }
        }
        if (!$this->isSupported()) {
            throw new SessionException('Sitzungen werden nicht unterstützt');
        }

        /*
         * keinen Cache-Header senden (Caching der API deaktivieren)
         * https://www.php.net/manual/de/function.session-cache-limiter.php
         */
        session_cache_limiter('');

        /*
         * PHP-Sitzung starten/wieder aufnehmen
         */
        if (session_start([
                'read_and_close' => $immediateUnlock
            ]) === false) {
            throw new SessionException('Sitzung konnte nicht gestartet oder fortgeführt werden');
        }

        $this->createdOrResumed = true;
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

    /**
     * @throws SessionException
     */
    public function getId(): string
    {
        $this->assertPresent();
        /*
         * TODO was liefert session_id wenn wir mit read_and_close gestartet haben?
         */
        return session_id();
    }

    /**
     * @throws SessionException
     */
    public function get(string $key): mixed
    {
        $this->assertPresent();
        return $_SESSION[$key];
    }

    /**
     * @throws SessionException
     */
    public function set(string $key, $value): void
    {
        $this->assertWriteable();
        $_SESSION[$key] = $value;
    }

    /**
     * @throws SessionException
     */
    public function map(string $key, callable $fn): void
    {
        $this->assertWriteable();
        $_SESSION[$key] = $fn($_SESSION[$key]);
    }

    /**
     * @throws SessionException
     */
    public function containsKey(string $key): bool
    {
        $this->assertPresent();
        return array_key_exists($key, $_SESSION);
    }

    /**
     * @throws SessionException
     */
    public function unset(string $key): void
    {
        $this->assertWriteable();
        unset($_SESSION[$key]);
    }

    /**
     * @throws SessionException
     */
    public function unlock(): void
    {
        $this->assertWriteable();
        session_write_close();
    }

    /**
     * @throws SessionException
     */
    public function clear(): void
    {
        $this->assertWriteable();
        /*
         * So werden nur die Daten aus der laufenden Sitzung gelöscht und die ID bleibt bestehen. session_destroy()
         * würde auch die ID löschen. Mit session.use_strict_mode müssen wir alte IDs nicht löschen, weil das Modul
         * keine Cookies akzeptiert, wenn keine zur ID gehörigen Daten vorhanden sind.
         * Siehe https://www.php.net/manual/de/function.session-destroy
         */
        session_unset();
    }

    public function destroy(string $id): void
    {
        $file = session_save_path() . DIRECTORY_SEPARATOR . "sess_" . $id;
        // PHP Fehler vermeiden, wenn nicht existierende Datei gelöscht würde
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}
