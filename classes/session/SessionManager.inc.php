<?php

/**
 * @file classes/session/SessionManager.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SessionManager
 * @ingroup session
 *
 * @brief Implements PHP methods for a custom session storage handler (see http://php.net/session).
 */

namespace PKP\session;

use PKP\config\Config;
use PKP\core\Registry;
use PKP\db\DAORegistry;

class SessionManager
{
    /** @var object The DAO for accessing Session objects */
    public $sessionDao;

    /** @var object The Session associated with the current request */
    public $userSession;

    /**
     * Constructor.
     * Initialize session configuration and set PHP session handlers.
     * Attempts to rejoin a user's session if it exists, or create a new session otherwise.
     *
     * @param $sessionDao SessionDAO
     * @param $request PKPRequest
     */
    public function __construct($sessionDao, $request)
    {
        $this->sessionDao = $sessionDao;

        // Configure PHP session parameters
        ini_set('session.use_trans_sid', 0);
        ini_set('session.serialize_handler', 'php');
        ini_set('session.use_cookies', 1);
        ini_set('session.name', Config::getVar('general', 'session_cookie_name')); // Cookie name
        ini_set('session.cookie_lifetime', 0);
        ini_set('session.cookie_path', Config::getVar('general', 'session_cookie_path', $request->getBasePath() . '/'));
        ini_set('session.cookie_domain', $request->getServerHost(null, false));
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_maxlifetime', 60 * 60);
        ini_set('session.auto_start', 1);
        ini_set('session.cache_limiter', 'none');

        session_set_save_handler(
            [$this, 'open'],
            [$this, 'close'],
            [$this, 'read'],
            [$this, 'write'],
            [$this, 'destroy'],
            [$this, 'gc']
        );

        // Initialize the session. This calls SessionManager::read() and
        // sets $this->userSession if a session is present.
        session_start();
        $sessionId = session_id();

        $ip = $request->getRemoteAddr();
        $userAgent = $request->getUserAgent();
        $now = time();

        // Check if the session is tied to the parent domain
        if (isset($this->userSession) && $this->userSession->getDomain() && $this->userSession->getDomain() != $request->getServerHost(null, false)) {
            // if current host contains . and the session domain (is a subdomain of the session domain), adjust the session's domain parameter to the parent
            if (strtolower(substr($request->getServerHost(null, false), -1 - strlen($this->userSession->getDomain()))) == '.' . strtolower($this->userSession->getDomain())) {
                ini_set('session.cookie_domain', $this->userSession->getDomain());
            }
        }

        if (!isset($this->userSession) || (Config::getVar('security', 'session_check_ip') && $this->userSession->getIpAddress() != $ip) || $this->userSession->getUserAgent() != substr($userAgent, 0, 255)) {
            if (isset($this->userSession)) {
                // Destroy old session
                session_destroy();
            }

            // Create new session
            $this->userSession = $this->sessionDao->newDataObject();
            $this->userSession->setId($sessionId);
            $this->userSession->setIpAddress($ip);
            $this->userSession->setUserAgent($userAgent);
            $this->userSession->setSecondsCreated($now);
            $this->userSession->setSecondsLastUsed($now);
            $this->userSession->setDomain(ini_get('session.cookie_domain'));
            $this->userSession->setSessionData('');

            $this->sessionDao->insertObject($this->userSession);
        } else {
            if ($this->userSession->getRemember()) {
                // Update session timestamp for remembered sessions so it doesn't expire in the middle of a browser session
                if (Config::getVar('general', 'session_lifetime') > 0) {
                    $this->updateSessionLifetime(time() + Config::getVar('general', 'session_lifetime') * 86400);
                } else {
                    $this->userSession->setRemember(0);
                    $this->updateSessionLifetime(0);
                }
            }

            // Update existing session's timestamp; will be saved when write is called
            $this->userSession->setSecondsLastUsed($now);
        }

        // Adding session_write_close as a shutdown function. This is a PHP
        // space workaround for the "Class '...' not found" bug in installations
        // having the APC opcode cache installed
        // Bugzilla: https://pkp.sfu.ca/bugzilla/show_bug.cgi?id=8151
        // PHP Bug tracker: https://bugs.php.net/bug.php?id=58739
        register_shutdown_function('session_write_close');
    }

    /**
     * Return an instance of the session manager.
     *
     * @return SessionManager
     */
    public static function getManager()
    {
        // Reference required
        $instance = & Registry::get('sessionManager', true, null);

        if (is_null($instance)) {
            $application = Registry::get('application');
            assert(!is_null($application));
            $request = $application->getRequest();
            assert(!is_null($request));

            // Implicitly set session manager by ref in the registry
            $instance = new SessionManager(DAORegistry::getDAO('SessionDAO'), $request);
        }

        return $instance;
    }

    /**
     * Get the session associated with the current request.
     *
     * @return Session
     */
    public function getUserSession()
    {
        return $this->userSession;
    }

    /**
     * Open a session.
     * Does nothing; only here to satisfy PHP session handler requirements.
     *
     * @return boolean
     */
    public function open()
    {
        return true;
    }

    /**
     * Close a session.
     * Does nothing; only here to satisfy PHP session handler requirements.
     *
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session data from database.
     *
     * @param $sessionId string
     *
     * @return boolean
     */
    public function read($sessionId)
    {
        if (!isset($this->userSession)) {
            $this->userSession = $this->sessionDao->getSession($sessionId);
            if (isset($this->userSession)) {
                $data = $this->userSession->getSessionData();
            }
        }
        return $data ?? '';
    }

    /**
     * Save session data to database.
     *
     * @param $sessionId string
     * @param $data array
     *
     * @return boolean
     */
    public function write($sessionId, $data)
    {
        if (isset($this->userSession)) {
            $this->userSession->setSessionData($data);
            $this->sessionDao->updateObject($this->userSession);
        }
        return true;
    }

    /**
     * Destroy (delete) a session.
     *
     * @param $sessionId string
     *
     * @return boolean
     */
    public function destroy($sessionId)
    {
        $this->sessionDao->deleteById($sessionId);
        return true;
    }

    /**
     * Garbage collect unused session data.
     * TODO: Use $maxlifetime instead of assuming 24 hours?
     *
     * @param $maxlifetime int the number of seconds after which data will be seen as "garbage" and cleaned up
     *
     * @return boolean
     */
    public function gc($maxlifetime)
    {
        return (bool) $this->sessionDao->deleteByLastUsed(time() - 86400, Config::getVar('general', 'session_lifetime') <= 0 ? 0 : time() - Config::getVar('general', 'session_lifetime') * 86400);
    }

    /**
     * Resubmit the session cookie.
     *
     * @param $sessionId string new session ID (or false to keep current ID)
     * @param $expireTime int new expiration time in seconds (0 = current session)
     *
     * @return boolean
     */
    public function updateSessionCookie($sessionId = false, $expireTime = 0)
    {
        $domain = ini_get('session.cookie_domain');
        // Specific domains must contain at least one '.' (e.g. Chrome)
        if (strpos($domain, '.') === false) {
            $domain = false;
        }

        // Clear cookies with no domain #8921
        if ($domain) {
            setcookie(session_name(), '', 0, ini_get('session.cookie_path'), false);
        }

        return setcookie(
            session_name(),
            ($sessionId === false) ? session_id() : $sessionId,
            $expireTime,
            ini_get('session.cookie_path'),
            $domain
        );
    }

    /**
     * Regenerate the session ID for the current user session.
     * This is useful to guard against the "session fixation" form of hijacking
     * by changing the user's session ID after they have logged in (in case the
     * original session ID had been pre-populated).
     *
     * @return boolean
     */
    public function regenerateSessionId()
    {
        $success = false;
        $currentSessionId = session_id();

        if (session_regenerate_id() && isset($this->userSession)) {
            // Delete old session and insert new session
            $this->sessionDao->deleteById($currentSessionId);
            $this->userSession->setId(session_id());
            $this->sessionDao->insertObject($this->userSession);
            $this->updateSessionCookie(); // TODO: this might not be needed on >= 4.3.3
            $success = true;
        }

        return $success;
    }

    /**
     * Change the lifetime of the current session cookie.
     *
     * @param $expireTime int new expiration time in seconds (0 = current session)
     *
     * @return boolean
     */
    public function updateSessionLifetime($expireTime = 0)
    {
        return $this->updateSessionCookie(false, $expireTime);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\PKP\session\SessionManager', '\SessionManager');
}
