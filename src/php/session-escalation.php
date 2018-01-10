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

require_once '../../default.php';
require_once '../../head.php';

?>
<a href="#" onclick='insert_form_div("edit")'>
    <p>Click!</p>
</a>
<?php
$user_name = "test2";
$user_password = "1234";
$session->escalate_session($user_name, $user_password);

//$session->close_escalated_session();
