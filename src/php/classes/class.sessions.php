<?php

/*
 * Copyright (C) 2017 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This class handles the session management, login, logout and permissions of users.
 * TODO: Provide a static variable $instance or save the session object in the $_SESSION array!
 * @todo maybe move const PRIVILEGE_ADMINISTRATION and the other privileges to the user class?
 * @author Martin Mandelkow
 */
class sessions {

    const PRIVILEGE_ADMINISTRATION = 'administration';
    const PRIVILEGE_CREATE_EMPLOYEE = 'create_employee';
    const PRIVILEGE_CREATE_ROSTER = 'create_roster';
    const PRIVILEGE_APPROVE_ROSTER = 'approve_roster';
    const PRIVILEGE_CREATE_OVERTIME = 'create_overtime';
    const PRIVILEGE_CREATE_ABSENCE = 'create_absence';
    const PRIVILEGE_REQUEST_OWN_ABSENCE = 'request_own_absence';

    public static $Pdr_list_of_privileges = array(
        self::PRIVILEGE_ADMINISTRATION,
        self::PRIVILEGE_CREATE_EMPLOYEE,
        self::PRIVILEGE_CREATE_ROSTER,
        self::PRIVILEGE_APPROVE_ROSTER,
        self::PRIVILEGE_CREATE_OVERTIME,
        self::PRIVILEGE_CREATE_ABSENCE,
        self::PRIVILEGE_REQUEST_OWN_ABSENCE,
    );

    /**
     * poEdit and gettext are not willing to include words, that are not in the source files.
     * Therefore we randomly include some words here, which are necessary.
     * Used in function build_checkbox_permission() in user-management.php
     * Used in session->exit_on_missing_privilege()
     */
    private function gettext_fake() {
        return TRUE;
        gettext('administration');
        gettext('create employee');
        gettext('create roster');
        gettext('approve roster');
        gettext('create overtime');
        gettext('create absence');
        gettext('request own absence');
    }

    public function __construct() {
        ini_set('session.use_strict_mode', '1'); //Do not allow non-initiaized sessions in order to prevent session fixation.
        global $config;
        /**
         * In case there are several instances of the program on the same machine,
         * we need a specific identifier for the different instances.
         * Therefore we define a specific session_name:
         */
        session_name('PDR' . md5($config["session_secret"])); //MUST be called before session_start()
        session_start();
        if (isset($_SESSION['number_of_times_redirected'])) {
            /**
             * @TODO: Check if this is correct!
             * <p lang=de>Sollte hier isset() oder !isset() stehen?
             * Was genau wird hier getestet?
             * Es geht sicherlich um die Nutzung von HTTPS.
             * Um das zu testen und zu erzwingen wurden redirects angelegt.
             * Damit diese nicht endlos laufen, werden sie in der $SESSION mitgezählt.
             * Muss diese Prüfug nun vor oder nach session_start(); stattfinden?
             * Vorher sollte $_SESSION in keinem Fall definiert sein. Oder?
             * Was passiert denn, wenn die Variable bereits vorher gesetzt wird?
             *
             * In welchem genauen Fall soll die Bedingung jetzt wahr werden?
             * </p>
             */
            $_SESSION['number_of_times_redirected'] = 0;
        }

        /*
         * Interpret $_SERVER values:
         */
        $request_uri = filter_input(INPUT_SERVER, "REQUEST_URI", FILTER_SANITIZE_URL);
        $http_host = filter_input(INPUT_SERVER, "HTTP_HOST", FILTER_SANITIZE_URL);
        $script_name = filter_input(INPUT_SERVER, "SCRIPT_NAME", FILTER_SANITIZE_URL);

        if ("localhost" != $http_host AND "" != $http_host) {
            header("strict-transport-security: max-age=31536000");
        }
        /**
         * Force HTTPS:
         * We make an exception for localhost. If data is not sent through the net, there is no absolute need for HTTPS.
         * People are still free to use it on their own. Administrators are able to force it in Apache (or any other web server).
         */
        if ("localhost" != $http_host AND "" != $http_host) {
            self::force_https();
        }

        /**
         * Force a new visitor to identify as a user (=login):
         * The redirect obviously is not necessary on the login-page and on the register-page.
         */
        $List_of_pages_accessible_without_login = array('login.php', 'register.php', 'webdav.php', 'lost_password.php', 'reset_lost_password.php', 'background_maintenance.php');
        if (
                false === $this->user_is_logged_in()
                and !in_array(basename($script_name), $List_of_pages_accessible_without_login)
        ) {
            $location = PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/login.php";
            header("Location:" . $location);
            die('<p>Bitte zuerst <a href="' . $location . '">einloggen</a></p>' . PHP_EOL);
        }
        $this->keep_alive();
    }

    private function keep_alive() {
        /*
         * e dot mortoray at ecircle dot com
         * There is a nuance we found with session timing out although the user is still active in the session.  The problem has to do with never modifying the session variable.
         * The GC will clear the session data files based on their last modification time.  Thus if you never modify the session, you simply read from it, then the GC will eventually clean up.
         * To prevent this you need to ensure that your session is modified within the GC delete time.  You can accomplish this like below.
         */
        if (!isset($_SESSION['last_access']) || (time() - $_SESSION['last_access']) > 60) {
            $_SESSION['last_access'] = time();
        }
        if (!isset($_SESSION['last_regenerate_id']) || (time() - $_SESSION['last_regenerate_id']) > 15 * 60) {
            session_regenerate_id(); //To prevent session fixation attacks we regenerate the session id every once in a while.
            $_SESSION['regenerate_id'] = time();
        }
    }

    /**
     * Check if the logged-in user has a specified permission
     *
     * @return boolean TRUE for exisiting permission, FALSE for missing permission.
     */
    public function user_has_privilege($privilege) {
        return ($_SESSION['user_object']->has_privilege($privilege));
    }

    public function exit_on_missing_privilege($privilege) {
        if (!$this->user_has_privilege($privilege)) {
            $user_dialog = new user_dialog();
            $request_uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
            $message = gettext('You are missing the necessary permission to use this page.')
                    . ' ' . gettext('Please contact the administrator if you feel this is an error.')
                    . ' ("' . localization::gettext(str_replace('_', ' ', $privilege))
                    . '" ' . gettext('is required for') . ' ' . basename($request_uri) . ')';
            $user_dialog->add_message($message, E_USER_ERROR);
            echo $user_dialog->build_messages();
            exit();
        }
    }

    public function create_message_on_missing_privilege($privilege, $error_type = E_USER_ERROR) {
        if (!$this->user_has_privilege($privilege)) {
            $user_dialog = new user_dialog();
            $message = gettext('You are missing the necessary permission to perform this action.')
                    . ' ' . gettext('Please contact the administrator if you feel this is an error.')
                    . ' ("' . localization::gettext(str_replace('_', ' ', $privilege))
                    . '" ' . gettext('is required.') . ')';
            $user_dialog->add_message($message, $error_type);
        }
    }

    public function login($user_name, $user_password, $redirect = TRUE) {
        $user_dialog = new user_dialog;
        if (empty($user_password) OR empty($user_name)) {
            $user_dialog->add_message("No login credentials were given.", E_USER_ERROR);
            return FALSE;
        }
        /*
         * Get user data:
         */
        $result = database_wrapper::instance()->run("SELECT `primary_key` FROM `users` WHERE `user_name` = :user_name AND `status` = 'active'", array('user_name' => $user_name));
        $user = NULL;
        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $user = new user($row->primary_key);
        }
        if (!$user instanceof user) {
            /*
             * In case the given username just does not exist.
             */
            $user_dialog->add_message("Dieser Nutzer existiert nicht.", E_USER_ERROR);
            return FALSE;
        }
        /*
         * Check for multiple failed login attempts
         * If a user has tried to login 3 times in a row, he is blocked for 5 minutes.
         * The number of failed attempts is reset to 0 on every successfull login.
         * TODO: Write a selenium test for this lock!
         */
        if (3 <= $user->failed_login_attempts and strtotime('-5min') <= strtotime($user->failed_login_attempt_time)) {
            $errorMessage = "Zu viele ungültige Anmeldeversuche. Der Benutzer wird für 5 Minuten gesperrt.";
            $user_dialog->add_message($errorMessage, E_USER_ERROR);
            return FALSE;
        }

        /*
         * Check the password:
         */
        if (NULL !== $user && $user->password_verify($user_password)) {
            /*
             * Fill $_SESSION data on success:
             */
            session_regenerate_id(); //To prevent session fixation attacks we regenerate the session id right before setting up login details.
            $_SESSION['user_object'] = $user;
            /*
             * Reset failed_login_attempts
             */
            $user->reset_failed_login_attempts();

            if (TRUE === $redirect) {

                if (!isset($_SESSION['number_of_times_redirected'])) {
                    $_SESSION['number_of_times_redirected'] = 0;
                }
                if (!empty($referrer)) {
                    if ($_SESSION['number_of_times_redirected'] < 3) {
                        $_SESSION['number_of_times_redirected']++;
                        $referrer = 'pages/menu-tiles.php';
                        //die("Location:" . $referrer);
                        header("Location:" . $referrer);
                    }
                } else {
                    if ($_SESSION['number_of_times_redirected'] < 3) {
                        $_SESSION['number_of_times_redirected']++;

                        header("Location:" . PDR_HTTP_SERVER_APPLICATION_PATH);
                    }
                }
            }
            return TRUE;
        } else {
            /*
             * Register failed_login_attempts
             */
            if ($user instanceof user) {
                $user->register_failed_login_attempt();
            }
            $errorMessage = "Benutzername oder Passwort war ungültig.";
            $user_dialog->add_message($errorMessage, E_USER_ERROR);
            return FALSE;
        }
        return FALSE;
    }

    public static function logout() {
        session_destroy();
        header("Location: " . PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/login.php");
    }

    function send_mail_about_lost_password(user $user, $token) {
        $user_dialog = new user_dialog();
        global $config;
        if (isset($config['application_name'])) {
            $application_name = $config['application_name'];
        } else {
            $application_name = 'PDR';
        }

        $message_subject = quoted_printable_encode(gettext('Lost password'));
        $message_text = quoted_printable_encode("<HTML><BODY>"
                . sprintf(gettext('Dear %1$s,\r\n\r\n in order to set a new password for'), $user->user_name)
                . " '"
                . $application_name
                . "' "
                . gettext("user name") . ": " . $user->get_user_name() . ", "
                . gettext("please visit")
                . " <a href='"
                . "https://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"])
                . "/reset_lost_password.php?"
                . "user_key=" . $user->get_primary_key()
                . "&token=$token'>"
                . gettext("this address")
                . ".</a>"
                . gettext("Your token is valid for 24 hours.")
                . "</BODY></HTML>");
        $headers = 'From: ' . $config['contact_email'] . "\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: quoted-printable";

        /*
         * TODO: Use PDR email class
         */
        $sent_result = mail($recipient, $message_subject, $message_text, $headers);
        if ($sent_result) {
            $message = "A lost password email was successfully sent.";
            error_log($message);
        } else {
            $message = "An error occured while sending the mail.";
            error_log($message);
        }
    }

    public function write_lost_password_token_to_database(user $user, $token) {
        if (!is_null($user) and !is_null($token)) {
            $user_key = $user->user_key;
            database_wrapper::instance()->run("DELETE FROM `users_lost_password_token` WHERE `time_created` <= NOW() - INTERVAL 1 DAY");
            $sql_query = "INSERT INTO `users_lost_password_token` (`user_key`, `token`) VALUES (:user_key, UNHEX(:token))";
            database_wrapper::instance()->run($sql_query, array('user_key' => $user_key, 'token' => $token));
            return TRUE;
        }
        return FALSE;
    }

    private static function force_https() {
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
            if (!isset($_SESSION['number_of_times_redirected'])) {
                $_SESSION['number_of_times_redirected'] = 0;
            }
            $https_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            if (!headers_sent() and ( $_SESSION['number_of_times_redirected'] ) < 3) {
                $_SESSION['number_of_times_redirected']++;
                header("Status: 301 Moved Permanently");
                header("Location: $https_url");
                die("<p>Dieses Programm erfordert die Nutzung von "
                        . "<a title='Article about HTTPS on german Wikipedia' href='https://de.wikipedia.org/w/index.php?title=HTTPS'>HTTPS</a>."
                        . " Nur so kann die Übertragung von sensiblen Daten geschützt werden.</p>\n");
            } elseif (( $_SESSION['number_of_times_redirected'] ) < 3) {
                $_SESSION['number_of_times_redirected']++;
                die('<script type="javascript">document.location.href="' . $https_url . '";</script>');
            } else {
                die("<p>Dieses Programm erfordert die Nutzung von "
                        . "<a title='Article about HTTPS on german Wikipedia' href='https://de.wikipedia.org/w/index.php?title=HTTPS'>HTTPS</a>."
                        . " Nur so kann die Übertragung von sensiblen Daten geschützt werden.</p>\n");
            }
        }
    }

    public function user_is_logged_in() {
        if (!empty($_SESSION['user_object'])
                and $_SESSION['user_object'] instanceof user
                and $_SESSION['user_object']->exists()) {
            return true;
        }
        return false;
    }
}
