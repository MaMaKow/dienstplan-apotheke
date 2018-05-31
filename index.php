<?php

/*
 *  Copyright (C) 2018 Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
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
 * Unfortunately we had a "301 Moved Permanently" to "tag-out.php" set on the old index.php
 * Therefore we have to keep that file there and make another redirect.
 * Some browsers might still take their users to the page "src/php/pages/roster-day-read.php"
 */

header("HTTP/1.1 307 Temporary Redirect");
header("Location: src/php/pages/menu-tiles.php");
exit();
?>
