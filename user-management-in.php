<?php
require 'default.php';
$workforce = new workforce();
$User_list = read_user_list_from_database();
$employee_id = user_input::get_variable_from_any_input('employee_id', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_employee_id']);
if (FALSE === in_array($employee_id, array_keys($User_list))) {
    /* This happens if a coworker does not have a user account (yet).
     * He can still be chosen within other pages.
     * Therefore we might get his/her id in the cookie.
     * Now we just change it to someone, who does have a user account:
     */
    $employee_id = min(array_keys($User_list));
}
create_cookie('employee_id', $employee_id, 30);

function insert_user_data_into_database() {
    $User["employee_id"] = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
    $User["privilege"] = filter_input(INPUT_POST, 'privilege', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    database_wrapper::instance()->beginTransaction();
    $sql_query = "DELETE FROM `users_privileges` WHERE `employee_id`  = :user";
    database_wrapper::instance()->run($sql_query, array('user' => $User["employee_id"]));

    foreach ($User["privilege"] as $privilege) {
        $sql_query = "INSERT INTO `users_privileges` (`employee_id`, `privilege`) VALUES(:user, :privilege)";
        database_wrapper::instance()->run($sql_query, array('user' => $User["employee_id"], 'privilege' => $privilege));
    }
    database_wrapper::instance()->commit();
}

if (filter_has_var(INPUT_POST, 'submit_user_data')) {
    insert_user_data_into_database();
}

function read_user_data_from_database($employee_id) {
    global $workforce;
    $sql_query = "SELECT * FROM `users` WHERE `employee_id` = :employee_id";
    $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
    while ($row = $result->fetch(PDO::FETCH_OBJ)) {
        $User["employee_id"] = $row->employee_id;
        $User["user_name"] = $row->user_name;
        $User["email"] = $row->email;
        $User["status"] = $row->status;
        $User["last_name"] = $workforce->List_of_employees[$row->employee_id]->last_name;
    }
    $sql_query = "SELECT * FROM `users_privileges` WHERE `employee_id` = :employee_id";
    $result = database_wrapper::instance()->run($sql_query, array('employee_id' => $employee_id));
    $User["privilege"] = array();
    while ($row = $result->fetch(PDO::FETCH_OBJ)) {
        $User["privilege"][] = $row->privilege;
    }
    return $User;
}

function read_user_list_from_database() {
    $sql_query = "SELECT `employee_id`, `user_name` FROM `users` ORDER BY `employee_id` ASC";
    $result = database_wrapper::instance()->run($sql_query);
    while ($row = $result->fetch(PDO::FETCH_OBJ)) {
        $User_list[$row->employee_id] = $row->user_name;
        $User_list[$row->employee_id] = new employee((int) $row->employee_id, $row->user_name, NULL, NULL, NULL, NULL, NULL);
    }
    return $User_list;
}

$User = read_user_data_from_database($employee_id);
require 'head.php';
require 'src/php/pages/menu.php';
if (!$session->user_has_privilege('administration')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zum Erstellen von Mitarbeitern fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}



echo build_html_navigation_elements::build_select_employee($employee_id, $User_list);

function build_checkbox_permission($privilege, $checked) {
    $privilege_name = gettext(str_replace('_', ' ', $privilege));
    $text = "<label for='$privilege'>" . $privilege_name . ": </label>";
    $text .= "<input type='checkbox' name='privilege[]' value='$privilege' id='$privilege' ";
    if ($checked) {
        $text .= " checked='checked'";
    }
    $text .= ">";
    return $text;
}
?>
<form method='POST' id='user_management'>
    <input type='text' name='employee_id' id="employee_id" value="<?= $User["employee_id"] ?>" hidden='true'>
    <p>
        <?php
        foreach (sessions::$Pdr_list_of_privileges as $privilege) {
            echo build_checkbox_permission($privilege, in_array($privilege, $User["privilege"]));
            echo "<br>";
        }
        ?>
    </p><p>

        <input type=submit id=save_new class='no-print' name=submit_user_data value='Eintragen' form='user_management'>
    </p>

</form>
</body>
</html>
