<?php
/*
 * Copyright (C) 2018 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once '../../../default.php';

$network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices();
$List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
$current_branch_id = user_input::convert_post_empty_to_php_null(user_input::get_variable_from_any_input('mandant', FILTER_SANITIZE_NUMBER_INT, $network_of_branch_offices->get_main_branch_id()));
if (filter_has_var(INPUT_POST, 'branch_id') and $session->user_has_privilege('administration')) {
    $new_branch_id = filter_input(INPUT_POST, "branch_id", FILTER_SANITIZE_NUMBER_INT);
    $new_branch_name = filter_input(INPUT_POST, "branch_name", FILTER_SANITIZE_SPECIAL_CHARS);
    $new_branch_short_name = filter_input(INPUT_POST, "branch_short_name", FILTER_SANITIZE_SPECIAL_CHARS);
    $new_branch_address = filter_input(INPUT_POST, "branch_address", FILTER_SANITIZE_SPECIAL_CHARS);
    $new_branch_manager = filter_input(INPUT_POST, "branch_manager", FILTER_SANITIZE_SPECIAL_CHARS);
    $new_branch_pep_id = user_input::convert_post_empty_to_php_null(filter_input(INPUT_POST, "branch_pep_id", FILTER_SANITIZE_NUMBER_INT));

    if (filter_has_var(INPUT_POST, 'remove_branch')) {
        $old_branch_id = filter_input(INPUT_POST, "branch_id", FILTER_SANITIZE_NUMBER_INT);
        $sql_query = "DELETE FROM `branch` WHERE `branch_id` = :branch_id";
        $result = database_wrapper::instance()->run($sql_query, array('branch_id' => $old_branch_id));
        if ('00000' === $result->errorCode()) {
            $message = "The branch was successfully deleted.";
            $user_dialog = new \user_dialog();
            $user_dialog->add_message($message, E_USER_NOTICE);
        } else {
            $message = "Error while trying to delete the branch.";
            $user_dialog = new \user_dialog();
            $user_dialog->add_message($message, E_USER_ERROR);
        }
    } elseif (!$network_of_branch_offices->branch_exists($new_branch_id)) {
        /*
         * This is a new branch.
         * We will simply insert it into the database table.
         */
        $sql_query = "INSERT INTO `branch` (`branch_id`, `name`, `short_name`, `address`, `manager`, `PEP`) VALUES (:branch_id, :name, :short_name, :address, :manager, :PEP);";
        $new_branch_data = array(
            'branch_id' => $new_branch_id,
            'name' => $new_branch_name,
            'short_name' => $new_branch_short_name,
            'address' => $new_branch_address,
            'manager' => $new_branch_manager,
            'PEP' => $new_branch_pep_id
        );
        database_wrapper::instance()->run($sql_query, $new_branch_data);
        $current_branch_id = $new_branch_id;
    } else {
        /*
         * This is a changed branch.
         * We will update it into the database table.
         */
        $sql_query = "UPDATE `branch` SET "
                . " `name` = :name,"
                . " `short_name` = :short_name,"
                . " `address` = :address,"
                . " `manager` = :manager,"
                . " `PEP` = :PEP"
                . " WHERE `branch_id` = :branch_id";
        $new_branch_data = array(
            'branch_id' => $new_branch_id,
            'name' => $new_branch_name,
            'short_name' => $new_branch_short_name,
            'address' => $new_branch_address,
            'manager' => $new_branch_manager,
            'PEP' => $new_branch_pep_id
        );
        database_wrapper::instance()->run($sql_query, $new_branch_data);
        $List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
        $current_branch_id = $new_branch_id;
    }

    $Opening_times_from = filter_input(INPUT_POST, 'opening_times_from', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
    $Opening_times_to = filter_input(INPUT_POST, 'opening_times_to', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
    /*
     * TODO: Move this into the branch class.
     */
    for ($weekday = 1; $weekday <= 7; $weekday++) {
        /*
         * Remove row from database if it existed
         */
        $sql_query = "DELETE FROM `opening_times` WHERE `branch_id` = :branch_id AND `weekday` = :weekday";
        $result = database_wrapper::instance()->run($sql_query, array(
            'branch_id' => $new_branch_id,
            'weekday' => $weekday,
        ));
        if ('' !== $Opening_times_from[$weekday] and '' !== $Opening_times_to[$weekday]) {
            $sql_query = "INSERT INTO `opening_times` "
                    . " SET `start` = :start, `end` = :end, `branch_id` = :branch_id, `weekday` = :weekday "
                    . " ON DUPLICATE KEY UPDATE `start` = :start2, `end` = :end2";
            $result = database_wrapper::instance()->run($sql_query, array(
                'branch_id' => $new_branch_id,
                'weekday' => $weekday,
                'start' => $Opening_times_from[$weekday],
                'start2' => $Opening_times_from[$weekday],
                'end' => $Opening_times_to[$weekday],
                'end2' => $Opening_times_to[$weekday],
            ));
        }
    }
}

/**
 * Reload branch data:
 */
$network_of_branch_offices->update_list_of_branch_objects();
$List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';

$session->exit_on_missing_privilege('administration');

//echo "<div class='centered-form-div'>";
echo "<div>";
if (empty($List_of_branch_objects)) {
    echo "<p>"
    . gettext("No pharmacy and no branches have been configured. Please setup at least one pharmacy!")
    . "</p>";
    $current_branch_id = 1;
}
$new_branch = new \PDR\Pharmacy\Branch(null);
$List_of_branch_objects[] = $new_branch;
echo build_html_navigation_elements::build_select_branch($current_branch_id, $List_of_branch_objects, NULL);

/**
 * <p>Define the $branch_object which will be edited.
 * In case there is no branch object yet, we will create an empty one.
 * </p>
 */
if ($network_of_branch_offices->branch_exists($current_branch_id)) {
    $branch_object = $List_of_branch_objects[$current_branch_id];
} else {
    /**
     * @todo Is there a more clean way to do this?
     * I probably do not want to create a class branch_object_empty, do I?
     */
    $branch_object = $new_branch;
}

/**
 * @todo <p>Perhaps make a group of builder classes.
 * And put the html building functions inside.</p>
 * @param \PDR\Pharmacy\Branch $branch_object
 * @return string
 */
function build_branch_input_opening_times($branch_object) {

    $string = "<fieldset>";
    $string .= "<legend>"
            . gettext('Opening times')
            . "</legend>";
    $string .= "<table id='branch_input_opening_times_fieldset_table'>";
    $Weekday_names = localization::get_weekday_names();
    foreach ($Weekday_names as $weekday_number => $weekday_name) {
        $string .= "<tr>";
        $string .= "<td>" . $weekday_name . "</td> ";
        $string .= "<td>" . gettext("from") . "</td> ";
        $string .= "<td><input type=time name=opening_times_from[$weekday_number] value='" . $branch_object->getOpeningTimes()[$weekday_number]['day_opening_start'] . "' form='branch_management_form' ></td> ";
        $string .= "<td>" . gettext("to") . "</td> ";
        $string .= "<td><input type=time name=opening_times_to[$weekday_number] value='" . $branch_object->getOpeningTimes()[$weekday_number]['day_opening_end'] . "' form='branch_management_form' ></td> ";
        $string .= "</tr>";
    }
    $string .= "</table>";
    $string .= "</fieldset>";
    return $string;
}
?>
<form accept-charset='utf-8' method='POST' id='branch_management_form'>
</form>

<div id="branchManagementFormDiv">

    <fieldset>
        <legend><?= gettext('Identifier'); ?></legend>
        <p>
            <label for="branch_id"><?= gettext('Branch Id') ?>: </label>
            <br>
            <input form="branch_management_form" type="text" name="branch_id" id="branch_id" value="<?= $current_branch_id ?>">
        </p>
        <p>
            <label for="branchPepId"><?= gettext('Branch pep id') ?>: </label>
            <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/information.svg"
                 class="inline-image"
                 title="<?= gettext("Awinta Smart and Awinta One have an option to export PEP data. If you uploaded such PEP data, enter your PEP Id for this branch here.") ?>">
            <br>
            <input form="branch_management_form" type='text' name='branch_pep_id' id="branchPepId" value="<?= $branch_object->getPEP(); ?>">
        </p>
    </fieldset>
    <fieldset>
        <legend><?= gettext('Master data'); ?></legend>
        <p>
            <label for="branch_name"><?= gettext('Branch name') ?>: </label>
            <br>
            <input form="branch_management_form" type='text' name='branch_name' id="branch_name" value="<?= $branch_object->getName(); ?>">
        </p><p>
            <label for="branchShortName"><?= gettext('Branch short name') ?>: </label>
            <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/information.svg"
                 class="inline-image"
                 title="<?= gettext("This is a short unofficial nickname for your pharmacy. It is used in pages with limited space. Please choose no more than 12 letters.") ?>">
            <br>
            <input form="branch_management_form" type='text' name='branch_short_name' id="branchShortName" maxlength="16" value="<?= $branch_object->getShortName(); ?>">
        </p><p>
            <label for="branch_address"><?= gettext('Branch address') ?>: </label>
            <br>
            <textarea form="branch_management_form" cols="50" rows="3" name='branch_address' id="branch_address" ><?= $branch_object->getAddress(); ?></textarea>
        </p><p>
            <label for="branch_manager"><?= gettext('Branch manager') ?>: </label>
            <br>
            <input form="branch_management_form" type='text' name='branch_manager' id="branch_manager" value="<?= $branch_object->getManager(); ?>">
        </p>
    </fieldset>
    <?= build_branch_input_opening_times($branch_object); ?>

    <div id="formButtonsContainer">
        <button type='submit' form='branch_management_form' id='submit_branch_data' class="form-button no-print">
            <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_save.svg">
            <p> <?= gettext("Save") ?>  </p>
        </button>
        <?php if (null !== $current_branch_id) { ?>
            <button type='submit' name="remove_branch" form='branch_management_form' id="branchFormButtonRemove" class="form-button no-print" onclick='return confirmDelete()'>
                <img src="<?= PDR_HTTP_SERVER_APPLICATION_PATH ?>img/md_delete_forever.svg">
                <p> <?= gettext("Remove branch") ?>  </p>
            </button>
        <?php } ?>
    </div>
</div>
</div><!--id = 'branch_management_main' -->

<?php require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php'; ?>

</body>
</html>
