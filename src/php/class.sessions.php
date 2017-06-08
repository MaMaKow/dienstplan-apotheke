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
//header("strict-transport-security: max-age=31536000");
//header("strict-transport-security: max-age=0");
if (empty($_SERVER["HTTPS"]) OR $_SERVER["HTTPS"] != "on") {
      header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
      exit();
    
}
*/

session_start();
if (!isset($_SESSION['userid']) and 'login.php' !== basename($_SERVER['SCRIPT_NAME']) and 'register.php' !== basename($_SERVER['SCRIPT_NAME']) ) {
    //test if the current file is on the top level or deeper in the second level.
    if(strpos(pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME), 'src/php')){
        $location = "login.php";
    } else {
        $location = "src/php/login.php";
    }
    header("Location:" . $location . "?referrer=" . $_SERVER["REQUEST_URI"]);
    die('Bitte zuerst <a href="' . $location . '?referrer=' . $_SERVER["REQUEST_URI"] . '">einloggen</a>');
}
/*
 * e dot mortoray at ecircle dot com ¶8 years ago
 * There is a nuance we found with session timing out although the user is still active in the session.  The problem has to do with never modifying the session variable. 
 * The GC will clear the session data files based on their last modification time.  Thus if you never modify the session, you simply read from it, then the GC will eventually clean up. 
 * To prevent this you need to ensure that your session is modified within the GC delete time.  You can accomplish this like below. 
 */
if (!isset($_SESSION['last_access']) || (time() - $_SESSION['last_access']) > 60)
    $_SESSION['last_access'] = time();

/**
 * Description of class
 *
 * @author Mandelkow
 */
class sessions {
//put your code here
}
