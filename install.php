<?php
/**   Dienstplan-Apotheke includes a database and HTML PHP interface for comprehensive management of a duty roster for pharmacies.
**    Copyright (C) 2016  Dr. Martin Mandelkow
**
**    This program is free software: you can redistribute it and/or modify
**    **it under the terms of the GNU General Public License as published by
**    the Free Software Foundation, either version 3 of the License, or
**    (at your option) any later version.
**
**    This program is distributed in the hope that it will be useful,
**    but WITHOUT ANY WARRANTY; without even the implied warranty of
**    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
**    GNU General Public License for more details.
**
**    You should have received a copy of the GNU General Public License
**    along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/
if (file_exists('./config/config.php')) {
  die ("The application seems to be already installed. Please see the <a href=install.php>configuration page</a> if you want to make any changes!");
}


require 'funktionen.php';

if (empty($_POST)) {
  //We might want to read some kind of standard values from a file:
  include "./config/default_config.php";
  $default_config = $config;
} else {
  //Read the POST values:
  foreach ($_POST as $key => $value) {
    if (!empty($_POST[$key])) {
        $new_config[$key] = sanitize_user_input($value);
    } elseif (isset($default_config[$key])) {
        $new_config[$key] = sanitize_user_input($default_config[$key]);
    }
  }
  $config = $new_config;
  //echo "<pre>"; var_export($_POST); echo "</pre>";
  //Create a config directory if it does not yet exist.
  if (!is_dir('./config')) {
    if (!mkdir('./config', 0664, true)) {
      die('Erstellung der Verzeichnisse schlug fehl...');
    }
  }
  if (!is_dir('./tmp')) {
    if (!mkdir('./tmp', 0664, true)) {
      die('Erstellung der Verzeichnisse schlug fehl...');
    }
  }
  if (!is_dir('./ics')) {
    if (!mkdir('./ics', 0664, true)) {
      die('Erstellung der Verzeichnisse schlug fehl...');
    }
  }
  if (!is_dir('./upload')) {
    if (!mkdir('./upload', 0664, true)) {
      die('Erstellung der Verzeichnisse schlug fehl...');
    }
  }

  //rename('./config/new_config.php', './config/config.php');
  if (file_exists('./config/config.php')) {
    rename('./config/config.php', './config/config.php_'.time());
  }
  file_put_contents('./config/config.php', '<?php  $config =' . var_export($new_config, true) . ';');
  chmod('./config/config.php', 0664);

  //Create the database, the database user and the tables:
  require 'db-verbindung.php'; //needs $config['database_user'], $config['database_password'] and $config['database_name']
  //$abfrage = "CREATE DATABASE IF NOT EXISTS `".$config['database_name']."` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;";
  $abfrage = "CREATE DATABASE IF NOT EXISTS `Apotheketest` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;";
  $ergebnis = mysqli_query($verbindungi, $abfrage) or error_log("Error: $abfrage <br>".mysqli_error($verbindungi)) and die("Error: $abfrage <br>".mysqli_error($verbindungi));
  // TODO: /var/www/html/phpBB3/install/index.php shows an example of the information needed.
}

//Define different error reporting options:
$error_error   = E_ERROR | E_USER_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_PARSE;
$error_warning = $error_error   | E_WARNING | E_USER_WARNING | E_CORE_WARNING | E_COMPILE_WARNING;
$error_notice  = $error_warning | E_NOTICE | E_USER_NOTICE | E_DEPRECATED | E_USER_DEPRECATED;
$error_all     = $error_notice  | E_STRICT;

//Check which error reporting strength has been set roughly. This is not precise!
if ($error_all <= $config['error_reporting']) {
    $error_all_checked="checked";
} elseif ($error_notice <= $config['error_reporting']) {
    $error_notice_checked="checked";
} elseif ($error_warning <= $config['error_reporting']) {
    $error_warning_checked="checked";
} elseif ($error_error <= $config['error_reporting']) {
    $error_error_checked="checked";
} else {
     /**
     * 
     * @param int $type
     * @return string|int
     */
  function FriendlyErrorType($type)
      {
          switch($type)
              {
              case E_ERROR: // 1 //
                  return 'E_ERROR';
              case E_WARNING: // 2 //
                  return 'E_WARNING';
              case E_PARSE: // 4 //
                  return 'E_PARSE';
              case E_NOTICE: // 8 //
                  return 'E_NOTICE';
              case E_CORE_ERROR: // 16 //
                  return 'E_CORE_ERROR';
              case E_CORE_WARNING: // 32 //
                  return 'E_CORE_WARNING';
              case E_CORE_ERROR: // 64 //
                  return 'E_COMPILE_ERROR';
              case E_CORE_WARNING: // 128 //
                  return 'E_COMPILE_WARNING';
              case E_USER_ERROR: // 256 //
                  return 'E_USER_ERROR';
              case E_USER_WARNING: // 512 //
                  return 'E_USER_WARNING';
              case E_USER_NOTICE: // 1024 //
                  return 'E_USER_NOTICE';
              case E_STRICT: // 2048 //
                  return 'E_STRICT';
              case E_RECOVERABLE_ERROR: // 4096 //
                  return 'E_RECOVERABLE_ERROR';
              case E_DEPRECATED: // 8192 //
                  return 'E_DEPRECATED';
              case E_USER_DEPRECATED: // 16384 //
                  return 'E_USER_DEPRECATED';
              }
          return $type;
      }
    $other_error = FriendlyErrorType($config['error_reporting']);
    //echo "Debug mode is not preconfigured:<br>".FriendlyErrorType($config['error_reporting'])."<br>";
    $other_error_html = '
    <div class="row">
      <div class="cell">
        <input type="radio" name="error_reporting" value="'.$other_error.'" checked>
        '.$other_error.' (current value)
      </div>
    </div>
';
}


//Get a list of supported encodings:
// TODO: is perhaps /usr/share/i18n/SUPPORTED better for supported encodings?
$datalist_encodings = "<datalist id='encodings'>\n";
$supported_encodings = mb_list_encodings();
foreach ($supported_encodings as $key => $supported_encoding) {
  $datalist_encodings .= "\t<option value='$supported_encoding'>\n";
}
$datalist_encodings .= "</datalist>\n";
//Get a list of supported locales:
$datalist_locales = "<datalist id='locales'>\n";
exec("locale -a", $exec_result);
foreach ($exec_result as $key => $installed_locale) {
  $datalist_locales .= "\t<option value='$installed_locale'>\n";
}
$datalist_locales .= "</datalist>\n";


//echo "<pre>"; var_export($new_config); echo "</pre>";


?>



<html>
<?php require 'head.php';?>
  <body>
    <div style=font-size:larger>
      <H1>Installation</H1>
      <p>Bitte ergänzen Sie die folgenden Werte um den Dienstplan zu konfigurieren.</p>
      <form class="" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="table">
          <div id="first_page">
          <div class="cell-header">
            Database settings
            <p class="hint">
              The installation script will create a new MySQL database or use an existing one.
              <br>
              All the information about the duty rosters will be stored password protected in this database.
            </p>
          </div>
          <div class="row">
            <div class="cell" width=1%>Application name
            </div>
            <div class="cell" width=1%><input type="text" name="application_name" value="<?php echo $config['application_name']?>">
            </div>
          </div>
          <div class="row">
            <div class="cell">Database name
            </div>
            <div class="cell"><input type="text" name="database_name" value="<?php echo $config['database_name']?>">
            </div>
          </div>
          <div class="row">
            <div class="cell">Database User
            </div>
            <div class="cell"><input type="text" name="database_user" value="<?php echo $config['database_user']?>">
            </div>
          </div>
          <div class="row">
            <div class="cell">User Password
            </div>
            <div class="cell"><input type="password" name="database_password" id="first_pass"
              onchange="compare_passwords()"
              onkeyup="compare_passwords()"
              onkeydown="compare_passwords()"
              onclick="compare_passwords()"
              onblur="compare_passwords()"
              onpaste="compare_passwords()"
              >
            </div>
            <div class="cell" width=90%>
              <img id="approve_pass_img"    style="display:none" src="images/approve.png" height="20em">
              <img id="disapprove_pass_img" style="display:none" src="images/disapprove.png" height="20em">
              <!--
              <input type="text" id=clear_pass>
          -->
            </div>
          </div>
          <div class="row">
            <div class="cell">Repeat Password
            </div>
            <div class="cell"><input type="password" id="second_pass"
                onchange="compare_passwords()"
                onkeyup="compare_passwords()"
                onkeydown="compare_passwords()"
                onclick="compare_passwords()"
                onblur="compare_passwords()"
                onpaste="compare_passwords()"
                >
            </div>
          </div>
          <button id="first_button" class="next">
            Next
          </button>
        </div>
          <div id="second_page" style="display:none">
          <div class="cell">
            Contact information
            <p class="hint">
              Viewing users will be invited to address wishes and suggestions to the editor of the duty rosters.
            </p>
          </th>
          <div class="row">
            <div class="cell">Email
            </div>
            <div class="cell"><input type="email" name="contact_email" value="<?php echo $config['contact_email']?>">
            </div>
          </div>
        </div><div id="third_page" style="display:none">
          <div class="cell">
            Technical details
            <p class="hint">
              Time values can be adapted to various local user's environments.
              <br>
              They depend on language and cultural conventions.
            </p>
          </th>
          <div class="row">
            <div class="cell">Locale
            </div>
            <div class="cell"><input list="locales" value="<?php echo $config['LC_TIME']?>" name="LC_TIME">
              <?php echo "$datalist_locales"; ?>
            </div>
          </div>
          <div class="row">
            <div class="cell">Charset
            </div>
            <div class="cell"><input list="encodings" value="<?php echo $config['mb_internal_encoding']?>" name="mb_internal_encoding">
              <?php echo "$datalist_encodings"; ?>
            </div>
          </div>
          <div class="cell"> Debugging
              <p class="hint"> Which type of errors should be reported to the user?</p>
          </th>
          <div class="row">
            <div class="cell">
              <input type="radio" name="error_reporting" value="<?php echo "$error_error\" $error_error_checked";?>>
              Only fatal errors
            </div>
          </div>
          <div class="row">
            <div class="cell">
              <input type="radio" name="error_reporting" value="<?php echo "$error_warning\" $error_warning_checked";?>>
              Also warnings
            </div>
          </div>
          <div class="row">
            <div class="cell">
              <input type="radio" name="error_reporting" value="<?php echo "$error_notice\" $error_notice_checked";?>>
              And notices
            </div>
          </div>
          <div class="row">
            <div class="cell">
              <input type="radio" name="error_reporting" value="<?php echo "$error_all\" $error_all_checked";?>>
              Everything
            </div>
          </div>
          <?php if (!empty($other_error_html)) {
            echo "$other_error_html";
          }?>
          <div class="cell">Approval
              <p class="hint">
                After a duty roster is planned, it has to be approved, before it is in effect.
                <br>
                Should viewers be able to see duty rosters before they are finally approved?
               </p>
          </th>
          <div class="row">
            <div class="cell"><input type="radio" name="hide_disapproved" value="true">Hide
            </div>
          </div>
          <div class="row">
            <div class="cell"><input type="radio" name="hide_disapproved" value="false" checked>Show
            </div>
          </div>
          <!--
          <div class="row">
            <div class="cell">
            </div>
            <div class="cell"><input type="text">
            </div>
          </div>
          <div class="row">
            <div class="cell">
            </div>
            <div class="cell"><input type="text">
            </div>
          </div>-->
        </div>
        </table>
        <input type="submit">
      </form>
    </div>
  </body>
</html>
