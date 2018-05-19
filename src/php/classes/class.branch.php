<?php

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

    static function read_branches_from_database() {
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

        if (!in_array(basename($script_name), array('branch-management.php'))) {
            $script_name = filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING);
            $request_uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
            $location = PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/branch-management.php';
            header('Location:' . $location);
            die('<p><a href="' . $location . '>Please configure at least one branch first!</a></p>');
        }
    }

}
