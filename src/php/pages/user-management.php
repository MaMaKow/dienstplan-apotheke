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
require '../../../default.php';
//$workforce = new workforce();
$user_dialog = new user_dialog();

$user_key = user_input::get_variable_from_any_input('user_key', FILTER_SANITIZE_NUMBER_INT, $_SESSION['user_object']->get_primary_key());
$user = new user($user_key);
create_cookie('user_key', $user_key, 30);

function insert_user_data_into_database(&$user) {
    global $session;
    if (!$session->user_has_privilege('administration')) {
        return FALSE;
    }

    $privileges = filter_input(INPUT_POST, 'privilege', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
    if ($_SESSION['user_object']->get_primary_key() == $user->get_primary_key() and $session->user_has_privilege('administration')) {
        /*
         * We want to avoid an administrator loosing the administration privilege by accident.
         * The privilege can only be lost, if an other administrator is taking it away.
         * This way we make sure, that there always is at least one user with administrative privileges.
         */
        if (!in_array('administration', $privileges)) {
            $privileges[] = 'administration';
            global $Error_message;
            $Error_message[] = "An administrative user cannot get rid of the 'administration' privilege himself. Only another administrator can take it away.";
        }
    }
    $user->write_new_privileges_to_database($privileges);
}

if (filter_has_var(INPUT_POST, 'submit_user_data')) {
    insert_user_data_into_database($user);
}

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$session->exit_on_missing_privilege('administration');
echo $user_dialog->build_messages();



echo build_html_navigation_elements::build_select_user($user_key);

function build_checkbox_permission($privilege, $checked) {
    $privilege_name = localization::gettext(str_replace('_', ' ', $privilege));
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
    <input type='text' name='user_key' id="user_key" value="<?= $user->get_primary_key(); ?>" hidden='true'>
    <p>
        <?php
        foreach (sessions::$Pdr_list_of_privileges as $privilege) {
            echo build_checkbox_permission($privilege, array_key_exists($privilege, $user->privileges));
            echo "<br>";
        }
        ?>
    </p><p>

        <input type=submit id=save_new class='no_print' name=submit_user_data value='Eintragen' form='user_management'>
    </p>

</form>
<?php
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/fragments/fragment.footer.php';
?>
</body>
</html>
