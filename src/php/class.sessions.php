<?php

/*
 * Copyright (C) 2017 Mandelkow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
 * e dot mortoray at ecircle dot com
 * There is a nuance we found with session timing out although the user is still active in the session.  The problem has to do with never modifying the session variable. 
 * The GC will clear the session data files based on their last modification time.  Thus if you never modify the session, you simply read from it, then the GC will eventually clean up. 
 * To prevent this you need to ensure that your session is modified within the GC delete time.  You can accomplish this like below. 
 */
if (!isset($_SESSION['last_access']) || (time() - $_SESSION['last_access']) > 60) {
    $_SESSION['last_access'] = time();
}

/**
 * This class handles the session management, login, logout and permissions of users.
 *
 * @author Mandelkow
 */
class sessions {
    /*
     * @var $Privileges array of permissions such as edit_roster.
     * The array is built in the form array(edit_roster => TRUE, create_roster => TRUE)
     * Permissions, which are not given to the user are not FALSE. The are simply not present.
     */

    private $Privileges;
    /*
     * @var $user_id int 
     */
    private $user_id;

    public function __construct() {
        session_id("regular");
        session_start();

        /*
         * Interpret $_SERVER values:
         */
        $request_uri = filter_input(INPUT_SERVER, "REQUEST_URI", FILTER_SANITIZE_URL);
        $http_host = filter_input(INPUT_SERVER, "HTTP_HOST", FILTER_SANITIZE_URL);
        $https = filter_input(INPUT_SERVER, "HTTPS", FILTER_SANITIZE_STRING);
        $script_name = filter_input(INPUT_SERVER, "SCRIPT_NAME", FILTER_SANITIZE_STRING);
        /*
         * Interpret $_SESSION values:
         */
        $this->user_id = $_SESSION['user_id'];

        /*
         * TODO: On a production server the max-age value should probably be set to one year:
         * header("strict-transport-security: max-age=31536000");
         * for now we present a value of one minute while writing and debugging the code.
         */
        header("strict-transport-security: max-age=60");
        /* Force HTTPS:
         * We make an exception for localhost. If data is not sent through the net, there is no absolute need for HTTPS.
         * People are still free to use it on their own. Administrators are able to force it in Apache (or any other web server).
         */
        if ("localhost" != $http_host AND ( empty($https) OR $https != "on")) {
            header("Location: https://" . $http_host . $request_uri);
            die("<p>Dieses Programm erfordert die Nutzung von <a title='Article about HTTPS on german Wikipedia' href='https://de.wikipedia.org/w/index.php?title=HTTPS'>HTTPS</a>. Nur so kann die Übertragung von sensiblen Daten geschützt werden.</p>");
        }

        /*
         * Force a new visitor to identify as a user (=login):
         * The redirect obviously is not necessary on the login-page and on the register-page.
         */
        if (!isset($_SESSION['user_id']) and 'login.php' !== basename($script_name) and 'register.php' !== basename($script_name)) {
            /*
             * Test if the current file is on the top level or deeper in the second level:
             */
            if (strpos(pathinfo($script_name, PATHINFO_DIRNAME), 'src/php')) {
                $location = "login.php";
            } else {
                $location = "src/php/login.php";
            }
            header("Location:" . $location . "?referrer=" . $request_uri);
            die('<p>Bitte zuerst <a href="' . $location . '?referrer=' . $request_uri . '">einloggen</a></p>');
        }
    }

    private function read_Privileges_from_database() {
        global $pdo;
        //$statement = $pdo->prepare("SELECT * FROM users_privileges WHERE `user_id` = :user_id");
        $statement = $pdo->prepare("SELECT * FROM users_privileges WHERE `user_id` = :user_id");
        $statement->execute(array('user_id' => $this->user_id));
        //$statement->debugDumpParams();
        while ($privilege_data = $statement->fetch()) {
            $this->Privileges[$privilege_data[privilege]] = TRUE;
        }
        return;
    }

    /*
     * Check if the logged-in user has a specefied permission
     * @return boolean TRUE for exisiting permission, FALSE for missing permission.
     */

    public function user_has_privilege($privilege) {
        if (empty($this->Privileges)) {
            /*
             * Privileges are read only once per session.
             * If permissions are revoked or granted during a session, this will take effect only after a logout(=session_destroy()).
             */
            $this->read_Privileges_from_database();
        }
        if (TRUE === $this->Privileges[$privilege]) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function escalate_session($user_name, $user_password) {
        session_write_close();
        session_id("escalated_session");
        session_start();
    }

}
