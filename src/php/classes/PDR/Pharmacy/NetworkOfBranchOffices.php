<?php

/*
 * Copyright (C) 2020 Mandelkow <netbeans@martin-mandelkow.de>
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
 * In German law one Pharmacist is allowed to own one main pharmacy and three branches.
 * This class holds the whole network of branch objects in the array $List_of_branch_objects.
 *
 * @see class branch
 * @author Mandelkow <netbeans@martin-mandelkow.de>
 */

namespace PDR\Pharmacy;

class NetworkOfBranchOffices {

    /**
     *
     * @var array The array contains all objects of the class branch.
     */
    private static $List_of_branch_objects;

    public function __construct() {
        if (empty(self::$List_of_branch_objects)) {
            /*
             * get a list of all the branches.
             */
            $Branch_ids = $this->read_branch_ids_from_database();
            self::$List_of_branch_objects = $this->read_branch_data_from_database($Branch_ids);
        }
    }

    /**
     *
     * Create all the branch objects.
     *
     * <p>This function takes a list of branch ids.
     * A branch object is created for each branch id.
     * The list of objects is then stored in the static array $List_of_branch_objects.</p>
     *
     * @param array $Branch_ids
     */
    private function read_branch_data_from_database(array $Branch_ids) {
        $List_of_branch_objects = array();
        foreach ($Branch_ids as $branch_id) {
            $List_of_branch_objects[$branch_id] = new \PDR\Pharmacy\Branch($branch_id);
        }
        return $List_of_branch_objects;
    }

    /**
     *
     * Get a list of branch ids from the database.
     *
     * <p>This function only gets the list of ids.
     * The function read_branch_data_from_database uses the list to create the branch objects.</p>
     *
     *
     * @return array List of branch ids.
     */
    private function read_branch_ids_from_database() {
        /*
         * Get a list of branches:
         */
        $Branch_ids = array();

        $sql_query = 'SELECT branch_id FROM `branch`;';
        $result = \database_wrapper::instance()->run($sql_query);
        while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $Branch_ids[] = $row->branch_id;
        }

        if (empty(\array_keys($Branch_ids))) {
            /**
             * In the case of missing branches, those HAVE TO be created.
             */
            $this->redirect_to_branch_management_form();
        }

        return $Branch_ids;
    }

    /**
     * Get the list of all branch objects.
     *
     * @return array $List_of_branch_objects
     */
    public function get_list_of_branch_objects() {
        if (empty(self::$List_of_branch_objects)) {
            new \PDR\Pharmacy\NetworkOfBranchOffices();
        }
        return self::$List_of_branch_objects;
    }

    /**
     * Read new branch data from the database after a change.
     *
     * Apart from the unset this method equals the __construct method.
     * @return array $List_of_branch_objects
     */
    public function update_list_of_branch_objects() {
        self::$List_of_branch_objects = array();
        $Branch_ids = $this->read_branch_ids_from_database();
        self::$List_of_branch_objects = $this->read_branch_data_from_database($Branch_ids);
    }

    /**
     * This function will guess, which branch is the main branch.
     * It will just use the one with the lowest branch_id.
     *
     * @return int The banch id of the main branch.
     */
    public function get_main_branch_id() {
        if (empty(self::$List_of_branch_objects)) {
            /*
             * We will return the number 1.
             * Obviously there are no branches yet.
             * The first one will be created soon.
             * It will most probably get the number 1.
             */
            return 1;
        }
        return \min(\array_keys(self::$List_of_branch_objects));
    }

    /**
     * Check if the given branch does exist.
     *
     * All existing branches are stored in the $List_of_branch_objects.
     *
     * @param int $branch_id
     * @return boolean True if the branch does exist.
     */
    public function branch_exists(int $branch_id = null) {
        if (\in_array($branch_id, \array_keys(self::$List_of_branch_objects))) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Redirect the browser to the branch management form
     *
     * If no branch is setup yet (e.g, directly after installation, or if all the branches have been deleted)
     * then the browser is redirected to the branch management form
     * @return void
     */
    private function redirect_to_branch_management_form() {
        global $session;
        if (false === $session->user_is_logged_in()) {
            /*
             * If we are not logged in yet, then there is no sense in redirecting.
             */
            return FALSE;
        }
        $script_name = \filter_input(\INPUT_SERVER, 'SCRIPT_NAME', \FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (in_array(basename($script_name), array('branch-management.php'))) {
            /*
             * If we are already on the page, then there is no sense in redirecting.
             */
            return FALSE;
        }
        $location = PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/branch-management.php';
        if (++$_SESSION['number_of_times_redirected'] < 4) {
            header('Location:' . $location);
        }
        die('<p><a href="' . $location . '">Please configure at least one branch first!</a></p>');
    }
}
