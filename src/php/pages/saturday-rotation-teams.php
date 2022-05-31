<?php
/*
 * Copyright (C) 2022 Mandelkow
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
require '../../../default.php';
$this_saturday_date_object = new DateTime("this saturday");

$workforce = new workforce();
$network_of_branch_offices = new \PDR\Pharmacy\NetworkOfBranchOffices;
$branch_id = user_input::get_variable_from_any_input("mandant", FILTER_SANITIZE_NUMBER_INT, $network_of_branch_offices->get_main_branch_id());
create_cookie("mandant", $branch_id, 30);
$saturday_rotation = new saturday_rotation($branch_id);
$Saturday_rotation_team_input_array = filter_input(INPUT_POST, "Saturday_rotation_team", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

function process_post_data($saturday_rotation, $branch_id, $Saturday_rotation_team_input_array, $session) {
    if (!$session->user_has_privilege(sessions::PRIVILEGE_CREATE_ROSTER)) {
        $session->create_message_on_missing_privilege(sessions::PRIVILEGE_CREATE_ROSTER, E_USER_ERROR);
        return NULL;
    }
    $team_id_input = array_key_first($Saturday_rotation_team_input_array);
    if ("" === $team_id_input) {
        $team_id_input = null;
    }
    $team_array_input = $Saturday_rotation_team_input_array[$team_id_input];
    $saturday_rotation->update_team_to_database($branch_id, $team_id_input, $team_array_input);
    /**
     * POST/REDIRECT/GET
     * We received data via POST.
     * The data has been processed.
     * We now redirect the user to the same page without posting data.
     * (Right now there is no data to specifically send via GET.
     *   The $branch_id is stored in a cookie.)
     */
    header('Location: ' . PDR_HTTP_SERVER_APPLICATION_PATH . 'src/php/pages/saturday-rotation-teams.php', true, 303);
    exit;
}

if (null !== $Saturday_rotation_team_input_array) {
    process_post_data($saturday_rotation, $branch_id, $Saturday_rotation_team_input_array, $session);
}

$List_of_branch_objects = $network_of_branch_offices->get_list_of_branch_objects();
$html_select_branch = build_html_navigation_elements::build_select_branch($branch_id, $List_of_branch_objects);

$List_of_teams = $saturday_rotation->List_of_teams;
$team_id_today = $saturday_rotation->get_participation_team_id($this_saturday_date_object);
/**
 * @var int $position_of_team_this_week is an information about where in the list of teams this $team_id_today is.
 */
$position_of_team_this_week = array_search($team_id_today, array_keys($List_of_teams));
/**
 * @var DateTime $first_saturday_date_object should be some date, where the first team (0) will be scheduled.
 */
$first_saturday_date_object = clone $this_saturday_date_object;
if (!empty($position_of_team_this_week)) { //<p lang=de>Wenn es keine Teams gibt, gibt es auch nichts zu verschieben. Das führt zu "Uncaught Exception: DateInterval::__construct(): Unknown or bad format (PW)"</p>
    $first_saturday_date_object->sub(new DateInterval('P' . $position_of_team_this_week . 'W'));
}
$saturday_date_object = clone $first_saturday_date_object;

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
$user_dialog = new user_dialog();
echo $user_dialog->build_messages();
echo "<script> var workforce = " . json_encode($workforce) . ";</script>";
?>
<?=
$html_select_branch;
?>
<br>
<br>
<br>
<br>
<br>
<p><?= sprintf(gettext('This Saturday is %1$s.'), $this_saturday_date_object->format("d.m.Y")) . " " ?>
    <?php
    if (isset($team_id_today) and false !== $team_id_today) {
        echo sprintf(gettext('It will be team %1$ss turn.'), $team_id_today);
    }
    ?></p>
<table id="saturday_rotation_team_input_table" data-max_team_id="<?= $saturday_rotation->get_maximum_team_id(); ?>">
    <tr>
        <th>Example date</th>
        <th>Team-Id</th>
        <th>Employee</th>
    </tr>
    <?php
    foreach ($List_of_teams as $team_id_should => $team_array_should) {
        $team_id = $team_id_should;
        $team_array = $saturday_rotation->List_of_teams[$team_id];
        ?>
        <tr data-team_id="<?= $team_id ?>">
            <td><?= $saturday_date_object->format('d.m.Y'); ?></td>
            <td>
                <span class="team_id_span">
                    <?= $team_id ?>
                </span>&nbsp;
                <?php if ($session->user_has_privilege(sessions::PRIVILEGE_CREATE_ROSTER)) { ?>
                    <a class="saturdayRotationTeamsRemoveTeamLink" onclick="saturdayRotationTeamsRemoveTeam(<?= $team_id . ", " . $branch_id ?>);">
                        - <?= gettext('Remove team'); ?>
                    </a>
                <?php } ?>
            </td>
            <td>
                <form method="POST">
                    <?php
                    $roster_row_iterator = 0;
                    foreach ($team_array as $employee_id) {
                        ?>
                        <span>
                            <?= $saturday_rotation->build_input_row_employee_select($employee_id, $team_id, $roster_row_iterator, $session); ?>
                        </span>
                        <?php
                        $roster_row_iterator++;
                    }
                    ?>
                    <?php if ($session->user_has_privilege(sessions::PRIVILEGE_CREATE_ROSTER)) { ?>

                        <span>
                            <a onclick="saturdayRotationTeamsAddEmployee(this);" >
                                + <?= gettext('Add another employee'); ?>
                            </a>
                        </span>
                    <?php } ?>
                </form>
            </td>
        </tr>
        <?php
        $saturday_date_object->add(new DateInterval('P1W'));
    }
    ?>
    <?php if ($session->user_has_privilege(sessions::PRIVILEGE_CREATE_ROSTER)) { ?>
        <tr>
            <td id="saturdayRotationTeamsAddTeamNextDate" data-saturdayRotationTeamsAddTeamNextDate="<?= $saturday_date_object->add(new DateInterval('P1W'))->format('d.m.Y'); ?>"></td>

            <td colspan="2" onclick="saturdayRotationTeamsAddTeam(this);" id="saturdayRotationTeamsAddTeamTd">
                <a>
                    +
                    <span><?= gettext('Add another team'); ?></span>
                </a>
            </td>
        </tr>
    <?php } ?>
</table>
</body>
</html>
