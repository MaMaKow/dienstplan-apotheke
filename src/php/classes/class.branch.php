<?php

class branch {
    /*
     * TODO: Make an object class branch!
     * Make an array of branch objects!
     */

    public $List_of_branches;
    public $name;
    public $short_name;
    public $address;
    public $manager;
    public $PEP;

    static function read_branches_from_database() {
        //Get a list of branches:
        /*
         * CAVE! This function is thought to be called from the outside of this class only.
         */
        if (!is_null($this)) {
            print_debug_variable("CAVE read_branches_from_database() is thought to be called from the outside of this class only.");
            return FALSE;
        }
        $sql_query = 'SELECT *
	FROM `branch`
	;';
        global $pdo;
        $statement = $pdo->prepare($sql_query);
        $statement->execute();

        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
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
            /*
             * TODO: redirect to the branch-management form if the script is not the branch-management form.
             * Also Show an error-message/hint in the branch-management, that no branch is existing.
             * TODO: Make this a function!
             * TODO: Return a single branch-array with allthe information.
             * Or learn to even make this an object.
             */
            $script_name = filter_input(INPUT_SERVER, "SCRIPT_NAME", FILTER_SANITIZE_STRING);
            $request_uri = filter_input(INPUT_SERVER, "REQUEST_URI", FILTER_SANITIZE_URL);

            if (!in_array(basename($script_name), array('branch-management.php'))) {
                $location = PDR_HTTP_SERVER_APPLICATION_PATH . "src/php/pages/branch-management.php";
                header("Location:" . $location . "?referrer=" . $request_uri);
                die('<p><a href="' . $location . '?referrer=' . $request_uri . '">Please configure at least one branch first!</a></p>');
            }
        } else {
            return $Branches;
        }
    }

}
