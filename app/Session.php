<?php
namespace Solvers\Dsql;

/*
 * Usage Example:
 * $s = new Session();
 * $s->sessionSet('foo', 'bar');
 * echo $s->sessionGet('foo');
 *
 * // compare session value
 * if (!$s->sessionCompare('foo', 'baz')) {
 *     echo 'baz is not equal to: ' . $s->sessionGet('foo');
 * }
 * // unset a session
 * $s->sessionUnset('bar');
 * // destroy whole session
 * $s->sessionEnd();
 */
class Session {

    /**
     * Initialize session on class __construct
     *
     * @param String $path optional session save path
     */
    public function __construct($path = false) {
        if ($path) {
            ini_set('session.save_path', realpath(dirname($_SERVER['DOCUMENT_ROOT']) . $path));
        }
        $this->sessionStart();
    }

    /**
     * Call session_start() if $_SESSION is not set
     */
    public function sessionStart(): void
    {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    /**
     * Get a session value based on it's key.
     * this will return to false if the session specified is not set
     *
     * @param String $key session key to fetch: $_SESSION[$key]
     *
     * @return Mixed $_SESSION[$key] value or false if not set
     */
    public function sessionGet(string $key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
    }

    /**
     * Set a session
     *
     * @param String $key session key to be set: $_SESSION[$key]
     * @param Mixed $value session value to be set
     */
    public function sessionSet(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Compare a session value
     *
     * @param String $key session key to be compared: $_SESSION[$key]
     * @param Mixed $expected expected value of $_SESSION[$key]
     * @param Bool $strict wether comparison must use `===` or `==`. Defaults to true
     *
     * @return Bool returns boolean wether $expected is equal to $_SESSION[$key]
     */
    public function sessionCompare(string $key, $expected, bool $strict = true): bool
    {
        $session = $this->sessionGet($key);
        if ($strict) {
            return $session === $expected;
        }
        return $session == $expected;
    }

    /**
     * Unset a session by key.
     * This will return to false if the session is not set in the first place
     *
     * @param String $key session key to unset: $_SESSION[$key]
     *
     * @return Bool returns wether $_SESSION[$key] is unset
     */

    public function sessionUnset(string $key): bool
    {
        $session = $this->sessionGet($key);
        if ($session) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }

    /**
     * Destroys the whole session
     *
     * @param Bool $compatibility (optional) Set wether we should use session_unset() for older deprecated code that does not use $_SESSION. defaults to false
     * @param Bool $exit (optional) call exit() function after destroy. Defaults to false
     */
    public function sessionEnd (bool $compatibility = false, $exit = false): void
    {
        if ($compatibility) { session_unset(); }
        session_destroy();
        if ($exit) {
            exit();
        }
    }
}