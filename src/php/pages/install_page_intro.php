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
?>
<h1>Introduction</h1>

<h2>Welcome to PDR!</h2>

<p>Pharmacy Duty Roster (PDR) is a web application that allows to operate a duty roster for pharmacies.
    PDR started in 2015 as an alternative to a really simple excel sheet without formulas.
    PDR aims to be user-friendly but at the same time cover all necessary features.
    PDR continuously strives to improve. It is open to your requests and wishes.
    I hope it will fulfill your expectations.</p>

<p>
    This installation system will guide you through installing PDR.

    For more information, I encourage you to read the installation guide.

    To read the PDR license or learn about obtaining support and our stance on it, please select the respective options from the side menu. To continue, please select the appropriate tab above.
</p>
<form action="install_page_welcome.php" method="post">
    <input type="submit" value="<?= gettext("Next") ?>">
</form>
