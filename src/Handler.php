<?php

declare(strict_types=1);

namespace MetaRush\CookieSessions;

use Defuse\Crypto;

class Handler implements \SessionHandlerInterface
{
    const MAX_COOKIE_LENGTH = 4000;

    /**
     *
     * @param string $key Encryption key generated from vendor/bin/generate-defuse-key
     * @param array<mixed> $options See "options" paramater in \setcookie()
     * @param string $cookiePrefix Cookie prefix
     */
    public function __construct(
        private string $key,
        private array $options = [],
        private $cookiePrefix = 'MRCS_')
    {
        // overide 'expires' option so that we use the ini directive
        $options['expires'] = \time() + \ini_get('session.gc_maxlifetime');
    }

    public function open($savePath, $sessionName): bool
    {
        \ob_start();
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($sid): string|false
    {
        if (!isset($_COOKIE[$this->cookiePrefix . $sid]))
            return '';

        $keyObject = Crypto\Key::loadFromAsciiSafeString($this->key);
        $decrypted = Crypto\Crypto::decrypt($_COOKIE[$this->cookiePrefix . $sid], $keyObject);

        return $decrypted;
    }

    public function write($sid, $value): bool
    {
        $keyObject = Crypto\Key::loadFromAsciiSafeString($this->key);
        $encrypted = Crypto\Crypto::encrypt($value, $keyObject);

        if (\strlen($encrypted) >= self::MAX_COOKIE_LENGTH) {
            \trigger_error('Cookie length (' . \strlen($encrypted) . ') >= ' . self::MAX_COOKIE_LENGTH . ', reduce your session variables', E_USER_WARNING);
            return false;
        }

        \setcookie($this->cookiePrefix . $sid, $encrypted, $this->options); // @phpstan-ignore-line

        return true;
    }

    public function destroy($sid): bool
    {
        foreach ($_COOKIE as $k => $v)
            if (0 === \strpos($k, $this->cookiePrefix)) {

                $path = '';
                if (\is_string($this->options['path']))
                    $path = $this->options['path'];

                \setcookie($k, '', -1, $path);
            }

        return true;
    }

    // garbage collection is done by the user's browser so this is not really needed
    public function gc($maxLifetime): int|false
    {
        return 1;
    }

}