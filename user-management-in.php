<?php
require 'default.php';
require 'db-lesen-mitarbeiter.php';
require 'db-lesen-mandant.php';
if (filter_has_var(INPUT_POST, "employee_id")) {
    $employee_id = filter_input(INPUT_POST, "employee_id", FILTER_VALIDATE_INT);
} elseif (filter_has_var(INPUT_GET, "employee_id")) {
    $employee_id = filter_input(INPUT_GET, "employee_id", FILTER_VALIDATE_INT);
} elseif (filter_has_var(INPUT_COOKIE, "employee_id")) {
    $employee_id = filter_input(INPUT_COOKIE, "employee_id", FILTER_VALIDATE_INT);
} else {
    $employee_id = $_SESSION['user_employee_id'];
}
if (isset($employee_id)) {
    create_cookie('employee_id', $employee_id, 30);
}


function insert_user_data_into_database() {
    $User["employee_id"] = filter_input(INPUT_POST, 'employee_id', FILTER_SANITIZE_NUMBER_INT);
    $User["privilege"] = filter_input(INPUT_POST, 'privilege', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);

    print_debug_variable($_POST, $User);
    mysqli_query_verbose("START TRANSACTION");
    $sql_query = "DELETE FROM `users_privileges` WHERE `employee_id`  = " . $User["employee_id"];
    mysqli_query_verbose($sql_query);

    foreach ($User["privilege"] as $privilege) {
        $sql_query = "INSERT INTO `users_privileges` (`employee_id`, `privilege`) VALUES('" . $User["employee_id"] . "', '" . $privilege . "')";
        mysqli_query_verbose($sql_query);
        print_debug_variable($sql_query);
    }
    mysqli_query_verbose("COMMIT");
}

if (filter_has_var(INPUT_POST, 'submit_user_data')) {
    insert_user_data_into_database();
}

function read_user_data_from_database($employee_id) {
    global $Mitarbeiter;
    $sql_query = "SELECT * FROM `users` WHERE `employee_id` = '$employee_id'";
    $result = mysqli_query_verbose($sql_query);
    while ($row = mysqli_fetch_object($result)) {
        $User["employee_id"] = $row->employee_id;
        $User["user_name"] = $row->user_name;
        $User["email"] = $row->email;
        $User["status"] = $row->status;
        $User["last_name"] = $Mitarbeiter[$row->employee_id];
    }
    $sql_query = "SELECT * FROM `users_privileges` WHERE `employee_id` = '$employee_id'";
    $result = mysqli_query_verbose($sql_query);
    while ($row = mysqli_fetch_object($result)) {
        $User["privilege"][] = $row->privilege;
    }
    return $User;
}

function read_user_list_from_database() {
    $sql_query = "SELECT `employee_id`, `user_name` FROM `users` ORDER BY `employee_id` ASC";
    $result = mysqli_query_verbose($sql_query);
    while ($row = mysqli_fetch_object($result)) {
        $User_list[$row->employee_id] = $row->user_name;
    }
    return $User_list;
}

$User = read_user_data_from_database($employee_id);
$User_list = read_user_list_from_database();
require 'head.php';
require 'navigation.php';
require 'src/php/pages/menu.php';
if (!$session->user_has_privilege('administration')) {
    echo build_warning_messages("", ["Die notwendige Berechtigung zum Erstellen von Mitarbeitern fehlt. Bitte wenden Sie sich an einen Administrator."]);
    die();
}



echo build_select_employee($employee_id, $User_list);

function build_checkbox_permission($privilege, $checked) {
    $text = "<label for='$privilege'>$privilege: </label>";
    $text .= "<input type='checkbox' name='privilege[]' value='$privilege' id='$privilege' ";
    if ($checked) {
        $text .= " checked='checked'";
    }
    $text .= ">";
    return $text;
}

?>
<form method='POST' id='user_management'>
    TODO: Get a good german wording!<br>

    <input type='text' name='employee_id' id="employee_id" value="<?= $User["employee_id"] ?>" hidden='true'>
    <p>
        <?php
        $sql_query = "SELECT DISTINCT `privilege` FROM `users_privileges`";
        $result = mysqli_query_verbose($sql_query);
        while ($row = mysqli_fetch_object($result)) {
            $Privilege_types[] = $row->privilege;
        }
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
