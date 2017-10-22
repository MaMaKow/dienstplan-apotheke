<?php

/**
 * *    Copyright (C) 2017  Dr. Martin Mandelkow
 * *
 * *    This program is free software: you can redistribute it and/or modify
 * *    **it under the terms of the GNU General Public License as published by
 * *    the Free Software Foundation, either version 3 of the License, or
 * *    (at your option) any later version.
 * *
 * *    This program is distributed in the hope that it will be useful,
 * *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 * *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * *    GNU General Public License for more details.
 * *
 * *    You should have received a copy of the GNU General Public License
 * *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * */
class install {

    function connect_to_database() {
        $database_management_system = "mysql"; //hard coded right now we do not offer any other dbms.
        $database_host = "localhost"; //TODO: This should be changed by input
        $database_name = "PDR"; //TODO: This should be changed by input
        $database_user = "PDR"; //TODO: This should be changed by input
        $database_password = "PDR"; //TODO: This should be changed by input
        try {
            $pdo = new PDO("$database_management_system:host=$database_host;charset=utf8;dbname=" . $database_name, $database_user, $database_password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
            global $pdo;
        } catch (PDOException $e) {
            ini_set("error_log", "error.log");
            error_log("Error!: " . $e->getMessage() . " in file:" . __FILE__ . " on line:" . __LINE__);
            die("<p>There was an error while querying the database. Please see the error log for more details!</p>");
        }
    }

    function setup_mysql_database() {
        /*
         * Create the database:
         */
        $statement = $pdo->prepare("CREATE DATABASE `:database_name`");
        $statement->exec(array(user_name => $user_name, database_host => $database_host, password => $password));

        /*
         * Create the user:
         */
        $statement = $pdo->prepare("CREATE USER ':user_name'@':database_host' IDENTIFIED BY ':password'");
        $statement->exec(array(user_name => $user_name, database_host => $database_host, password => $password));
        /*
         * Grant the user access to the database:
         */
        $statement = $pdo->prepare("GRANT ALL PRIVILEGES ON `:database_name` . * TO ':user_name'@':database_host'");
        $statement->exec(array(user_name => $user_name, database_host => $database_host, password => $password));
        /*
         * Reload the privileges:
         */
        $pdo->exec("FLUSH PRIVILEGES");
    }

    public static function handle_user_input_administration() {

        $user_name = filter_input(INPUT_POST, "user_name", FILTER_SANITIZE_STRING, $options = null);
        $employee_id = filter_input(INPUT_POST, "employee_id", FILTER_SANITIZE_INT, $options = null);
        $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL, $options = null);
        $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING, $options = null);
        $password2 = filter_input(INPUT_POST, "password2", FILTER_SANITIZE_STRING, $options = null);
        if ($password !== $password2) {
            global $error_message;
            $error_message = gettext("The passwords aren't the same!");
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $statement = $pdo->prepare("INSERT INTO users (user_name, employee_id, password, email, status) VALUES (:user_name, :employee_id, :password, :email, 'inactive')");
            $result = $statement->execute(array('user_name' => $user_name, 'employee_id' => $employee_id, 'password' => $password_hash, 'email' => $email));
        }
    }

    public static function pdr_directories_are_writable() {
        //TODO WHERE ARE WE? WHERE IS THE ROOT?
        $List_of_directories = array("upload", "tmp", "config");
        foreach ($List_of_directories as $directory_name) {
            if (is_writable($directory_name)) {
                $List_of_writable_directories[] = $directory_name;
            } else {
                $List_of_non_writable_directories[] = $directory_name;
            }
        }
    }

}
