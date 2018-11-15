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

/*
 * Pharmacies and connected branches
 *
 * In German law one Pharmacist is allowed to own one main pharmacy and three branches.
 * Each branch is led by one pharmacist branch manager.
 *
 * It is possible to create unlimited theoretical branches like "Field service", "Compounding" or "Hospital" to pricisely define, who is working where and for which task at which time.
 *
 * @author Dr. Martin Mandelkow
 */

class branch {

    public $name;
    public $short_name;
    public $address;
    public $manager;
    public $PEP;

    /*
     * read the branch data from the database
     * @return array An array ob objects of the class branch
     */

    public static function read_branches_from_database() {
        /*
         * Get a list of branches:
         * CAVE! This function is thought to be called from the outside of this class only.
         */
        if (!empty($this)) {
            error_log("CAVE read_branches_from_database() is thought to be called from the outside of this class only.");
            return FALSE;
        }
        $sql_query = 'SELECT *
	FROM `branch`
	;';
        $result = database_wrapper::instance()->run($sql_query);

        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            if ($row->short_name != "") {
                $Branches[$row->branch_id] = new branch();
                $Branches[$row->branch_id]->name = $row->name;
                $Branches[$row->branch_id]->short_name = $row->short_name;
                $Branches[$row->branch_id]->address = $row->address;
                $Branches[$row->branch_id]->manager = $row->manager;
                $Branches[$row->branch_id]->PEP = $row->PEP;
            }
        }

        if (empty(array_keys($Branches))) {
            branch::redirect_to_input_form_on_missing_setup();
        } else {
            return $Branches;
        }
    }

    /*
     * redirect the browser to the branch management form
     *
     * If no branch is setup yet (e.g, directly after installation, or if all the branches have been deleted)
     * then the browser is redirected to the branch management form
     * @return void
     */

    private static function redirect_to_input_form_on_missing_setup() {
        if (!isset($_SESSION['user_employee_id'])) {
            /*
             * If we are not logged in yet, then there is no sense in redirecting.
             */
            return FALSE;
        }
        $script_name = filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING);
        if (!in_array(basename($script_name), array('branch-management.php'))) {
            $location = PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/branch-management.php';
            header('Location:' . $location);
            die('<p><a href="' . $location . '>Please configure at least one branch first!</a></p>');
        }
    }

}
