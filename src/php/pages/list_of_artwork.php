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
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'head.php';
require PDR_FILE_SYSTEM_APPLICATION_PATH . 'src/php/pages/menu.php';
?>
<main>
    <?= gettext('This is a list of works of art that are part of pharmacy duty roster. It is listed for each work as far as is known title, author, source and license.'); ?>
    <ol>
        <li>
            <img class="example_artwork" src="../../../img/md_ic_settings_24px.svg"><br>
            <span class="cc-by_title">Settings</span>,
            <span class="cc-by_author">Material Design icons by Google</span>,
            <span class="cc-by_source"><a href="https://github.com/google/material-design-icons/blob/master/action/svg/design/ic_settings_24px.svg">GitHub</a></span>,
            <span class="cc-by_license"><a href="https://www.apache.org/licenses/LICENSE-2.0.html">Apache license version 2.0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/md_person-24px.svg"><br>
            <span class="cc-by_title">Person</span>,
            <span class="cc-by_author">Material Design icons by Google</span>,
            <span class="cc-by_source"><a href="https://www.google.com/design/icons/">material icons library</a></span>,
            <span class="cc-by_license"><a href="https://www.apache.org/licenses/LICENSE-2.0.html">Apache license version 2.0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/md_card_travel-24px.svg"><br>
            <span class="cc-by_title">card travel</span>,
            <span class="cc-by_author">Material Design icons by Google</span>,
            <span class="cc-by_source"><a href="https://www.google.com/design/icons/">material icons library</a></span>,
            <span class="cc-by_license"><a href="https://www.apache.org/licenses/LICENSE-2.0.html">Apache license version 2.0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/md_thumb_up-24px.svg"><br>
            <span class="cc-by_title">thumb up</span>,
            <span class="cc-by_author">Material Design icons by Google</span>,
            <span class="cc-by_source"><a href="https://www.google.com/design/icons/">material icons library</a></span>,
            <span class="cc-by_license"><a href="https://www.apache.org/licenses/LICENSE-2.0.html">Apache license version 2.0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/md_thumb_down-24px.svg"><br>
            <span class="cc-by_title">thumb down</span>,
            <span class="cc-by_author">Material Design icons by Google</span>,
            <span class="cc-by_source"><a href="https://www.google.com/design/icons/">material icons library</a></span>,
            <span class="cc-by_license"><a href="https://www.apache.org/licenses/LICENSE-2.0.html">Apache license version 2.0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/md_today-24px.svg"><br>
            <span class="cc-by_title">today</span>,
            <span class="cc-by_author">Material Design icons by Google</span>,
            <span class="cc-by_source"><a href="https://www.google.com/design/icons/">material icons library</a></span>,
            <span class="cc-by_license"><a href="https://www.apache.org/licenses/LICENSE-2.0.html">Apache license version 2.0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/md_date_range-24px.svg"><br>
            <span class="cc-by_title">date range</span>,
            <span class="cc-by_author">Material Design icons by Google</span>,
            <span class="cc-by_source"><a href="https://www.google.com/design/icons/">material icons library</a></span>,
            <span class="cc-by_license"><a href="https://www.apache.org/licenses/LICENSE-2.0.html">Apache license version 2.0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/employee_2.svg"><br>
            <span class="cc-by_title">Unternehmen Arzt Apotheker</span>,
            <span class="cc-by_author"><a href="https://pixabay.com/de/users/martex-2358270/">martex</a></span>,
            <span class="cc-by_source"><a href="https://pixabay.com/de/vectors/unternehmen-arzt-apotheker-person-1316931/">Pixabay</a></span>,
            <span class="cc-by_license"><a href="https://pixabay.com/de/service/license/">Pixabay License</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/information.svg"><br>
            <span class="cc-by_title" property="dct:title">Information Info Symbol Kreis</span>,
            <span class="cc-by_author"><a href="https://pixabay.com/de/users/Clker-Free-Vector-Images-3736">Clker-Free-Vector-Images</a></span>,
            <span class="cc-by_source"><a href="https://pixabay.com/de/vectors/informationen-info-symbol-kreis-41225/">Pixabay</a></span>,
            <span class="cc-by_license"><a href="https://pixabay.com/de/service/license/">Pixabay License</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/user_1.svg"><br>
            <span class="cc-by_title">Benutzer Person Menschen Profil</span>,
            <span class="cc-by_author"><a href="https://pixabay.com/de/users/tuktukdesign-3181967/">TukTukDesign</a></span>,
            <span class="cc-by_source"><a href="https://pixabay.com/de/vectors/benutzer-person-menschen-profil-1633248/">Pixabay</a></span>,
            <span class="cc-by_license"><a href="https://pixabay.com/de/service/license/">Pixabay License</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/watch_overtime.svg"><br>
            <span class="cc-by_title">Uhr Icon Handgelenk isoliert Uhren</span>,
            <span class="cc-by_author"><a href="https://pixabay.com/de/users/tuktukdesign-3181967/">TukTukDesign</a></span>,
            <span class="cc-by_source"><a href="https://pixabay.com/de/vectors/uhr-icon-handgelenk-isoliert-uhren-1633262/">Pixabay</a></span>,
            <span class="cc-by_license"><a href="https://pixabay.com/de/service/license/">Pixabay License</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/save.png"><br>
            <span class="cc-by_title">Save</span>,
            <span class="cc-by_author"><a href="https://www.flaticon.com/authors/smashicons">Smashicons</a></span>,
            <span class="cc-by_source"><a href="https://www.flaticon.com/free-icon/save_149654">Flaticon</a></span>,
            <span class="cc-by_license"><a href="https://file000.flaticon.com/downloads/license/license.pdf">Flaticon Basic License</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/pencil-pictogram.svg"><br>
            <span class="cc-by_title">Lapis-icon</span>,
            <span class="cc-by_author"><a href="https://commons.wikimedia.org/wiki/User:Vikiano">Vikiano</a></span>,
            <span class="cc-by_source"><a href="https://commons.wikimedia.org/wiki/File:Lapis-icon.png">Wikimedia</a></span>,
            <span class="cc-by_license"><a href="https://creativecommons.org/licenses/by-sa/4.0/deed.en">CC-BY-SA</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/add.svg"><br>
            <span class="cc-by_title" property="dct:title">Add</span>,
            <span class="cc-by_author"><a rel="dct:publisher" href="https://github.com/MaMaKow/dienstplan-apotheke"><span property="dct:title">Martin Mandelkow</span></a></span>,
            <span class="cc-by_source"><a href="https://github.com/MaMaKow/dienstplan-apotheke/blob/master/img/add.svg">Dienstplan Apotheke</a></span>,
            <span class="cc-by_license"><a rel="license" href="http://creativecommons.org/publicdomain/zero/1.0/">CC0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/backward.png"><br>
            <span class="cc-by_title" property="dct:title">Backward</span>,
            <span class="cc-by_author"><a rel="dct:publisher" href="https://github.com/MaMaKow/dienstplan-apotheke"><span property="dct:title">Martin Mandelkow</span></a></span>,
            <span class="cc-by_source"><a href="https://github.com/MaMaKow/dienstplan-apotheke/blob/master/img/backward.png">Dienstplan Apotheke</a></span>,
            <span class="cc-by_license"><a rel="license" href="http://creativecommons.org/publicdomain/zero/1.0/">CC0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/forward.png"><br>
            <span class="cc-by_title" property="dct:title">Forward</span>,
            <span class="cc-by_author"><a rel="dct:publisher" href="https://github.com/MaMaKow/dienstplan-apotheke"><span property="dct:title">Martin Mandelkow</span></a></span>,
            <span class="cc-by_source"><a href="https://github.com/MaMaKow/dienstplan-apotheke/blob/master/img/forward.png">Dienstplan Apotheke</a></span>,
            <span class="cc-by_license"><a rel="license" href="http://creativecommons.org/publicdomain/zero/1.0/">CC0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/copy.svg"><br>
            <span class="cc-by_title" property="dct:title">Copy</span>,
            <span class="cc-by_author"><a rel="dct:publisher" href="https://github.com/MaMaKow/dienstplan-apotheke"><span property="dct:title">Martin Mandelkow</span></a></span>,
            <span class="cc-by_source"><a href="https://github.com/MaMaKow/dienstplan-apotheke/blob/master/img/copy.svg">Dienstplan Apotheke</a></span>,
            <span class="cc-by_license"><a rel="license" href="http://creativecommons.org/publicdomain/zero/1.0/">CC0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/delete.svg"><br>
            <span class="cc-by_title" property="dct:title">Delete</span>,
            <span class="cc-by_author"><a rel="dct:publisher" href="https://github.com/MaMaKow/dienstplan-apotheke"><span property="dct:title">Martin Mandelkow</span></a></span>,
            <span class="cc-by_source"><a href="https://github.com/MaMaKow/dienstplan-apotheke/blob/master/img/delete.svg">Dienstplan Apotheke</a></span>,
            <span class="cc-by_license"><a rel="license" href="http://creativecommons.org/publicdomain/zero/1.0/">CC0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/download.png"><br>
            <span class="cc-by_title" property="dct:title">Download</span>,
            <span class="cc-by_author"><a rel="dct:publisher" href="https://github.com/MaMaKow/dienstplan-apotheke"><span property="dct:title">Martin Mandelkow</span></a></span>,
            <span class="cc-by_source"><a href="https://github.com/MaMaKow/dienstplan-apotheke/blob/master/img/download.png">Dienstplan Apotheke</a></span>,
            <span class="cc-by_license"><a rel="license" href="http://creativecommons.org/publicdomain/zero/1.0/">CC0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/edit-icon.svg"><br>
            <span class="cc-by_title" property="dct:title">Edit</span>,
            <span class="cc-by_author"><a rel="dct:publisher" href="https://github.com/MaMaKow/dienstplan-apotheke"><span property="dct:title">Martin Mandelkow</span></a></span>,
            <span class="cc-by_source"><a href="https://github.com/MaMaKow/dienstplan-apotheke/blob/master/img/edit-icon.svg">Dienstplan Apotheke</a></span>,
            <span class="cc-by_license"><a rel="license" href="http://creativecommons.org/publicdomain/zero/1.0/">CC0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/history.svg"><br>
            <span class="cc-by_title" property="dct:title">History</span>,
            <span class="cc-by_author"><a rel="dct:publisher" href="https://github.com/MaMaKow/dienstplan-apotheke"><span property="dct:title">Martin Mandelkow</span></a></span>,
            <span class="cc-by_source"><a href="https://github.com/MaMaKow/dienstplan-apotheke/blob/master/img/history.svg">Dienstplan Apotheke</a></span>,
            <span class="cc-by_license"><a rel="license" href="http://creativecommons.org/publicdomain/zero/1.0/">CC0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/new.svg"><br>
            <span class="cc-by_title" property="dct:title">New</span>,
            <span class="cc-by_author"><a rel="dct:publisher" href="https://github.com/MaMaKow/dienstplan-apotheke"><span property="dct:title">Martin Mandelkow</span></a></span>,
            <span class="cc-by_source"><a href="https://github.com/MaMaKow/dienstplan-apotheke/blob/master/img/new.svg">Dienstplan Apotheke</a></span>,
            <span class="cc-by_license"><a rel="license" href="http://creativecommons.org/publicdomain/zero/1.0/">CC0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/read-icon.svg"><br>
            <span class="cc-by_title" property="dct:title">Read</span>,
            <span class="cc-by_author"><a rel="dct:publisher" href="https://github.com/MaMaKow/dienstplan-apotheke"><span property="dct:title">Martin Mandelkow</span></a></span>,
            <span class="cc-by_source"><a href="https://github.com/MaMaKow/dienstplan-apotheke/blob/master/img/read-icon.svg">Dienstplan Apotheke</a></span>,
            <span class="cc-by_license"><a rel="license" href="http://creativecommons.org/publicdomain/zero/1.0/">CC0</a></span>
        </li>
        <li>
            <img class="example_artwork" src="../../../img/week_1.svg"><br>
            <span class="cc-by_title" property="dct:title">Week</span>,
            <span class="cc-by_author"><a rel="dct:publisher" href="https://github.com/MaMaKow/dienstplan-apotheke"><span property="dct:title">Martin Mandelkow</span></a></span>,
            <span class="cc-by_source"><a href="https://github.com/MaMaKow/dienstplan-apotheke/blob/master/img/week_1.svg">Dienstplan Apotheke</a></span>,
            <span class="cc-by_license"><a rel="license" href="http://creativecommons.org/publicdomain/zero/1.0/">CC0</a></span>
        </li>
        <!--
            <li>
                <img class="example_artwork" src="../../../img/"><br>
                <span class="cc-by_title"></span>,
                <span class="cc-by_author"></span>,
                <span class="cc-by_source"><a href=""></a></span>,
                <span class="cc-by_license"><a href=""></a></span>
            </li>
        -->
    </ol>
</main>
</body>
</html>
