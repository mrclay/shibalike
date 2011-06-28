<?php

namespace Shibalike;

/**
 * The state manager is responsible for tying a User object and some other state
 * to a user, usually via a session.
 */
interface IStateManager {

    /**
     * Forget all state data
     */
    public function forget();

    /**
     * Persist any session data to disk (e.g. called before a redirect)
     */
    public function writeClose();
    
    /**
     * @param string $key
     * @return string|null
     */
    public function get($key);
    
    /**
     * @param string $key
     * @param string $value if null, this key will be removed
     * @return bool
     */
    public function set($key, $value = null);
    
    /**
     * Returns true if user is likely to have state data (e.g. the session cookie)
     * @return bool
     */
    public function likelyHasState();
}
