# CHANGELOG

## 2.0.0 - 2020-05-17

### Changed

- Replace Exception with `\trigger_error()` in case user is writing sessions that exceeds `Handler::MAX_COOKIE_LENGTH`.
This is because you won't be able to catch exceptions on custom session handlers, at least at the time of this writing.
`\trigger_error()` is the next best alternative so that the user can at least handle it with custom error handlers.

- Add "return false" statement in the `write()` method of the `Handler` class to explicitly inform user that the session
was not written due to `Handler::MAX_COOKIE_LENGTH` issues.

## 1.0.2 - 2020-05-02

### Fixed

- Fix "throw new Exception" in `write()` method of `Handler` class.

## 1.0.1 - 2019-04-25

### Fixed

- Fix expired session cookies not being deleted.

## 1.0.0 - 2019-04-24

- Release first version.