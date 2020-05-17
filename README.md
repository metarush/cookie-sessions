# metarush/cookie-sessions

Storageless sessions using encrypted cookies as session handler

This library is a drop-in replacement for traditional session stores like
filesystem, database, memory, etc.. Use this library to leverage browser
cookies as session store and gain scalability without the maintenance of
traditional session stores.

## Install

Install via composer as `metarush/cookie-sessions`

## Usage

1. Generate an encryption key by typing `vendor/bin/generate-defuse-key` in your terminal.

2. Define cookie options (refer to the **options** parameter of the [\setcookie()](https://www.php.net/manual/en/function.setcookie.php) function).

```php
<?php

$options = [
    'path' => '/',
    'secure' => true,
    'httponly' => true
];
```

Note: Don't set `expires` option, this library will use the `session.gc_maxlifetime` ini directive instead.

3. Set the custom session handler on top of your script.

```php
$secretKey = 'replace this with the generated key';
$cookiePrefix = 'your_identifier'; // optional cookie prefix, keep it short, alphanumeric with _ suffix (e.g., XYZ_)
$handler = new \MetaRush\CookieSessions\Handler($secretKey, $options, $cookiePrefix);
session_set_save_handler($handler, true);
session_start();
```

4. Use `$_SESSIONS` normally

```php
$_SESSIONS['foo'] = 'bar';
```

## Notes

- This library uses `defuse/php-encryption` for encrypting session data in cookies
- Keep the `$secretKey` hidden from public
- Browsers generally have 4,000 bytes total cookie limit per domain
- An E_USER_WARNING will be thrown if you're trying to set a session variable that is equivalent to >= 4,000 bytes (in encrypted form)
- The limit counts data in encrypted form which is equivalent to roughly 1,900 of unencrypted data
- The limit doesn't account for other session data already written or scripts using cookies on the same domain
- Minimize session/cookie variables per domain to give way to other scripts if applicable