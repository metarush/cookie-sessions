<?php

declare(strict_types=1);

namespace MetaRush\CookieSessions;

use Defuse\Crypto;

class Handler implements \SessionHandlerInterface
{
    const MAX_COOKIE_LENGTH = 4000;
    private $key;
    private $options;
    private $cookiePrefix;

    /**
     *
     * @param string $key Encryption key generated from vendor/bin/generate-defuse-key
     * @param array $options See "options" paramater in \setcookie()
     * @param type $cookiePrefix Cookie prefix
     */
    public function __construct(string $key, ?array $options = [], $cookiePrefix = 'MRCS_')
    {
        // overide 'expires' option so that we use the ini directive
        $options['expires'] = \time() + \ini_get('session.gc_maxlifetime');

        $this->key = $key;
        $this->options = $options;
        $this->cookiePrefix = $cookiePrefix;
    }

    public function open($savePath, $sessionName)
    {
        \ob_start();
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($sid)
    {
        if (!isset($_COOKIE[$this->cookiePrefix . $sid]))
            return '';

        $keyObject = Crypto\Key::loadFromAsciiSafeString($this->key);
        $decrypted = Crypto\Crypto::decrypt($_COOKIE[$this->cookiePrefix . $sid], $keyObject);

        return $decrypted;
    }

    public function write($sid, $value)
    {
        $keyObject = Crypto\Key::loadFromAsciiSafeString($this->key);
        $encrypted = Crypto\Crypto::encrypt($value, $keyObject);

        if (\strlen($encrypted) >= self::MAX_COOKIE_LENGTH)
            throw new Exception('Cookie length >= ' . self::MAX_COOKIE_LENGTH . ' , reduce your session variables');

        \setcookie($this->cookiePrefix . $sid, $encrypted, $this->options);

        return true;
    }

    public function destroy($sid)
    {
        foreach ($_COOKIE as $k => $v)
            if (0 === \strpos($k, $this->cookiePrefix)) {
                $path = $this->options['path'] ?? '';
                \setcookie($k, '', -1, $path);
            }

        return true;
    }

    public function gc($maxLifetime)
    {
        return true;
    }
}
