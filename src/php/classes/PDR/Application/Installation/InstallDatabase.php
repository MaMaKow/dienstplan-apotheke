<?php

/*
 * Copyright (C) 2024 Mandelkow
 *
 * Dienstplan Apotheke
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace PDR\Application\Installation;

/**
 * Description of installDatabase
 *
 * @author Mandelkow
 */
class InstallDatabase {

    private $databaseExistedBeforeInstallation;
    private $databaseUserSelfExistedBeforeInstallation;
    private $pdo;

    public function setupMysqlDatabase() {
        $installConfiguration = new InstallConfiguration();
        $installUtility = new InstallUtility();
        $databaseName = $installConfiguration->getDatabaseName();
        if (FALSE === $this->createDatabase($databaseName)) {
            /*
             * We could not create the database.
             * This function is only called by handle_user_input_database(), if the database did not exist. So there is nothing we can do.
             */
            $installUtility->addErrorMessage("Could not connect to the database. Please check the configuration!");
            return FALSE;
        }
        /**
         * Try to define an own user with minimal privileges:
         */
        $userSelf = "pdr_" . \bin2hex(\openssl_random_pseudo_bytes(5)); //The user name must not be longer than 16 chars in mysql.
        $installConfiguration->setDatabaseUserSelf($userSelf);
        $passphraseSelf = \bin2hex(\openssl_random_pseudo_bytes(16));
        $installConfiguration->setDatabasePassphraseSelf($passphraseSelf);
        $host = $installConfiguration->getDatabaseHost();
        $this->databaseUserSelfExistedBeforeInstallation = $this->userExists($userSelf);
        if (TRUE === $this->databaseUserSelfExistedBeforeInstallation or TRUE === $this->createUser($userSelf, $passphraseSelf, $host)) {
            /*
             * We created our own user.
             * It should have a small set of privileges at only the pdr database:
             */
            if (TRUE === $this->setup_mysql_database_grant_privileges()) {
                /*
                 * Change the configuration to the new database user:
                 */
                $this->pdo->exec("FLUSH PRIVILEGES");
                $installConfiguration->setDatabaseUser($user);
                $installConfiguration->setDatabasePassphrase($passphraseSelf);
                $installConfiguration->unsetDatabaseUserSelf();
                $installConfiguration->unsetDatabasePassphraseSelf();
                return TRUE;
            } else {
                /**
                 * We created our own user. But we could not grant privileges to it.
                 * Therefore we will delete the user now.
                 * But only, if it did not exist in the first place.
                 */
                if (FALSE === $this->databaseUserSelfExistedBeforeInstallation) {
                    $statement = $this->pdo->prepare("DROP USER :database_user");
                    $statement->execute(array(
                        "database_user" => $installConfiguration->getDatabaseUserSelf(),
                    ));
                }
                /*
                 * That is too bad. We have to go back to the given user.
                 */
                $installConfiguration->unsetDatabaseUserSelf();
                $installConfiguration->unsetDatabasePassphraseSelf();
            }
        } else {
            /*
             * The user could not be created.
             */
            $installConfiguration->unsetDatabaseUserSelf();
            $installConfiguration->unsetDatabasePassphraseSelf();
            /*
             * We still return TRUE.
             * This user is not the ideal case. But it will work.
             * At least it was able to create the database and the tables.
             */
            return TRUE;
        }
    }

    private function createDatabase($databaseName) {
        /**
         * Test if the database exists:
         */
        $this->databaseExistedBeforeInstallation = $this->databaseExists($databaseName);
        if (TRUE === $this->databaseExistedBeforeInstallation) {
            /*
             * The database already exists.
             * There is nothing more to do here.
             */
            return TRUE;
        }
        /**
         * Create the database:
         */
        $statement = $this->pdo->prepare("CREATE DATABASE " . \database_wrapper::quote_identifier($databaseName) . ";");
        $result = $statement->execute();
        if (FALSE === $result) {
            /*
             * CAVE: Avoid $this->pdo->errorInfo()[3] in order to allow PHP below 5.4 to at least see, that the minimum version of PHP required is above 7.0.
             */
            error_log("Could not CREATE DATABASE with name: " . $databaseName);
        }
        return $result;
    }

    public function connectToDatabaseFromConfig(\PDR\Application\Installation\InstallConfiguration $installConfiguration) {
        $databaseName = $installConfiguration->getConfiguration()['database_name'];
        $userName = $installConfiguration->getConfiguration()['database_user'];
        $passphrase = $installConfiguration->getConfiguration()['database_password'];
        $databaseManagementSystem = $installConfiguration->getConfiguration()['database_management_system'];
        $host = $installConfiguration->getConfiguration()['database_host'];
        $port = $installConfiguration->getConfiguration()['database_port'];
        $this->connectToDatabase($databaseName, $userName, $passphrase, $databaseManagementSystem, $host, $port);
    }

    /**
     * Connect to the given database
     */
    public function connectToDatabase(string $databaseName, string $userName, string $passphrase, string $databaseManagementSystem, string $host, int $port = 3306) {
        $databaseConnectString = $databaseManagementSystem . ":";
        $databaseConnectString .= "host=" . $host . ";";
        $databaseConnectString .= $port ? "port=" . $port . ";" : "";
        $databaseConnectString .= "charset=utf8;";
        $databaseConnectOptions = array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
        try {
            $this->pdo = new \PDO($databaseConnectString, $userName, $passphrase, $databaseConnectOptions);
        } catch (\PDOException $e) {
            error_log("Error!: " . $e->getMessage() . " in file:" . __FILE__ . " on line:" . __LINE__);
            $installUtility = new \PDR\Application\Installation\InstallUtility();
            $installUtility->addErrorMessage("<p>There was an error while connecting to the database. Please see the error log for more details!</p>");
            echo $installUtility->buildErrorMessageDiv();
            die("Could not connect to the database.");
        }
        if (!$this->databaseExists($databaseName)) {
            /**
             * Bei der ersten Verbindung mit mysql existiert die Datenbank vermutlich noch nicht.
             * Die Verbindung zum Server steht aber erfolgreich.
             */
            return NULL;
        }
        $this->pdo->exec("USE " . $databaseName);
        return $this->pdo->errorInfo();
    }

    /**
     * <p lang=de>
     * Wenn die Datenbank nicht korrekt erstellt werden konnte, so sollte sie möglichst wieder entfernt werden.
     * Das sollte aber nur passieren, wenn es sie vorher noch nicht gegeben hat.
     * </p>
     */
    public function removeDatabase($databaseName) {
        if (TRUE === $this->database_existed_before_installation) {
            return FALSE;
        }
        $statement = $this->pdo->prepare("DROP DATABASE :database_name");
        $result = $statement->execute(array(
            "database_name" => $databaseName,
        ));
        return $result;
    }

    public function createTables(\PDR\Application\Installation\InstallConfiguration $configuration) {
        /**
         * Some tables have contraints.
         * In theory there would be a specific perfect order of table creation.
         * The referenced tables have to be created first.
         * As a workaround we store the tables, which could not be created.
         * After all tables have been tried to create the array of failed statements is executed again.
         * This is repeated, until no failed statements are left.
         */
        $this->connectToDatabaseFromConfig($configuration);
        /**
         * Start with the tables, for constraints:
         */
        $this->pdo->prepare(file_get_contents($configuration->getPdrFileSystemApplicationPath() . "src/sql/" . "absence_reasons.sql"))->execute(); //for absence
        $this->pdo->prepare(file_get_contents($configuration->getPdrFileSystemApplicationPath() . "src/sql/" . "branch.sql"))->execute(); //for approval, emergency_services, employees, opending_times, principle_roster, ...
        $this->pdo->prepare(file_get_contents($configuration->getPdrFileSystemApplicationPath() . "src/sql/" . "employees.sql"))->execute(); //for Dienstplan, Stunden, absence, principle_roster, users
        $this->pdo->prepare(file_get_contents($configuration->getPdrFileSystemApplicationPath() . "src/sql/" . "users.sql"))->execute(); //users_lost_password_token, users_privileges

        /**
         * Now follow all the other tables:
         * @todo get pdr_file_system_application_path from config or something utility
         */
        $sqlFiles = glob($configuration->getPdrFileSystemApplicationPath() . "src/sql/*.sql");
        $listOfFailedStatements = array();
        $numberOfExecutions = 0;
        foreach ($sqlFiles as $sqlFileName) {
            $sqlCreateTableStatement = file_get_contents($sqlFileName);
            $pattern = "/^.*TRIGGER.*\$/m";
            if (preg_match_all($pattern, $sqlCreateTableStatement, $matches)) {
                /*
                 * This file contains a CREATE TRIGGER clause.
                 */
                /*
                 * Remove DEFINER clause. MySQL will automatically add the current user.
                 */
                $pattern = "/^(.*)DEFINER[^@][^\s]*(.*)\$/m";
                $sqlCreateTableStatement = preg_replace($pattern, "$1 $2", $sqlCreateTableStatement);
            }
            $statement = $this->pdo->prepare($sqlCreateTableStatement);
            try {
                $result = $statement->execute();
            } catch (Exception $exception) {
                $listOfFailedStatements[] = $statement;
                error_log($exception->getMessage());
            }
        }
        while (array() !== $listOfFailedStatements) {
            if (5 <= $numberOfExecutions++) {
                /*
                 * This loop will try to install all the tables.
                 * But it will only try 5 iterations of the whole array.
                 */
                error_log(print_r($statement->errorInfo(), TRUE));
                error_log("Error while creating the database tables. Not all tables could be created.");
                error_log("Failed statements:");
                print_debug_variable($listOfFailedStatements);
                //TODO: Report also to the administrator on the screen.
                break;
            }
            foreach ($listOfFailedStatements as $key => $failed_statement) {
                try {
                    $result = $failed_statement->execute();
                    if (TRUE === $result) {
                        unset($listOfFailedStatements[$key]);
                    }
                } catch (\Exception $exception) {
                    error_log($exception->getMessage());
                }
            }
        }
        return $result;
    }

    /**
     * Test if the database exists:
     */
    private function databaseExists($databaseName) {
        $statement = $this->pdo->prepare("SHOW DATABASES LIKE :database_name;");
        $statement->execute(array(
            "database_name" => $databaseName,
        ));
        while ($row = $statement->fetch(\PDO::FETCH_NUM)) {
            if (!empty($row[0])) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Test if some database user exists:
     */
    private function userExists($database_user_name) {
        $statement = $this->pdo->prepare("SELECT COUNT(*) AS user_exists FROM INFORMATION_SCHEMA.USER_PRIVILEGES WHERE GRANTEE LIKE :database_user_name;");
        $result = $statement->execute(array(
            "database_user_name" => "'" . $database_user_name . "'@%",
        ));
        while ($row = $statement->fetch(\PDO::FETCH_OBJ)) {
            if (1 <= $row->user_exists) {
                return TRUE;
            }
        }
        return FALSE;
    }

    private function currentMysqlDatabaseUserHasPrivilege(string $privilege): bool {
        $statementShowGrants = $this->pdo->prepare("SHOW GRANTS;");
        $statementShowGrants->execute();
        while ($row = $statementShowGrants->fetch(\PDO::FETCH_ASSOC)) {
            foreach ($row as $value) {
                if (str_contains($value, $privilege)) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    private function createUser($databaseUser, $databasePassword, $databaseHost) {
        /*
         * Create the user:
         */
        if (!$this->currentMysqlDatabaseUserHasPrivilege("CREATE USER")) {
            error_log("The database user " . $databaseUser . " could not be created. Missing the CREATE USER privilege.");
            return FALSE;
        }
        $clientHostString = "localhost";
        if ("localhost" !== $databaseHost and "127.0.0.1" !== $databaseHost and "::1" !== $databaseHost) {
            /*
             * Allow access from any remote.
             */
            $clientHostString = "%";
        }

        $statementCreateUser = $this->pdo->prepare("CREATE USER :database_user@:database_host IDENTIFIED BY :database_password");
        $resultCreateUser = $statementCreateUser->execute(array(
            'database_user' => $databaseUser,
            'database_password' => $databasePassword,
            'database_host' => $clientHostString,
        ));
        /*
         * PDOStatement::execute will return TRUE on success or FALSE on failure.
         */
        if ($resultCreateUser) {
            error_log("The database user " . $databaseUser . " was created.");
            return TRUE;
        } else {
            error_log("The database user " . $databaseUser . " could not be created.");
            return FALSE;
        }
    }

    private function grantPrivileges($databaseName, $databaseHost, $databaseUser) {
        /*
         * Grant the user access to the database:
         * If the host is not localhost, then the access is allowed from ANY remote client.
         *
         */
        $privileges = array(
            "SELECT",
            "INSERT",
            "UPDATE",
            "DELETE",
            "CREATE",
            "DROP",
            "INDEX",
            "ALTER",
            "TRIGGER",
        );
        $clientHostString = "localhost";
        if ("localhost" !== $databaseHost and "127.0.0.1" !== $databaseHost and "::1" !== $databaseHost) {
            $clientHostString = "%";
        }
        $statement = $this->pdo->prepare("GRANT " . implode(", ", $privileges) . " ON " . \database_wrapper::quote_identifier($databaseName) . ".* TO :database_user@:client_host");
        $result = $statement->execute(array(
            'database_user' => $databaseUser,
            'client_host' => $clientHostString,
        ));

        error_log("GRANT all neccessary privileges to the database user on the client host: $clientHostString.");
        return $result;
    }

    public function fillDatabaseTables() {
        /**
         * Fill some tables with necessary data:
         */
        $Sql_query_array[] = "INSERT INTO `absence_reasons` (`id`, `reason_string`) VALUES
                (1, 'vacation'),
                (2, 'remaining vacation'),
                (3, 'sickness'),
                (4, 'sickness of child'),
                (5, 'taken overtime'),
                (6, 'paid leave of absence'),
                (7, 'maternity leave'),
                (8, 'parental leave');";
        foreach ($Sql_query_array as $sql_query) {
            $result = $this->pdo->query($sql_query);
            if ('00000' !== $result->errorCode()) {
                $this->pdo->rollBack();
                return FALSE;
            }
        }
    }

    /**
     * Store the state of the table structure in form of a hash inside the database
     */
    public function writePdrDatabaseVersionHash(\PDR\Application\Installation\InstallUtility $installUtility): void {
        require_once $installUtility->getPdrFileSystemApplicationPath() . 'src/php/database_version_hash.php';
        $statement = $this->pdo->prepare("REPLACE INTO `pdr_self` (`pdr_database_version_hash`) VALUES (:pdr_database_version_hash);");
        $statement->execute(array(
            'pdr_database_version_hash' => PDR_DATABASE_VERSION_HASH
        ));
    }

    /**
     * Grant all privileges to the administrative user:
     */
    public function setAdministratorPrivileges(\PDR\Application\Installation\InstallUtility $installUtiity) {
        $statement = $this->pdo->prepare("INSERT IGNORE INTO `users_privileges` (`user_key`, `privilege`) VALUES (:user_key, :privilege)");
        foreach (\sessions::$Pdr_list_of_privileges as $privilege) {
            $result = $statement->execute(array(
                "user_key" => $_SESSION['user_object']->get_primary_key(),
                "privilege" => $privilege
            ));
            if (FALSE === $result) {
                /*
                 * We were not able to create the administrative user.
                 */
                $installUtiity->addErrorMessage(gettext("Error while trying to create administrative user privileges."));
                error_log(var_export($statement->ErrorInfo(), TRUE));
                echo "<br>\n";
                return FALSE;
            }
        }
        return TRUE;
    }
}
