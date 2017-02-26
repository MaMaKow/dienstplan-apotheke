<?php
if (!file_exists('./config/config.php')) {
  die ("The application does not seem to be installed. Please see the <a href=install.php>installation page</a>!");
}

require 'funktionen.php';

if (empty($_POST)) {
    //If there allready is a configuration, then we will shows those values
    include "./config/config.php";
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
      error_log('Erstellung der Verzeichnisse schlug fehl...');
      die('Erstellung der Verzeichnisse schlug fehl...');
    }
  }
  if (!is_dir('./tmp')) {
    if (!mkdir('./tmp', 0664, true)) {
      error_log('Erstellung der Verzeichnisse schlug fehl...');
      die('Erstellung der Verzeichnisse schlug fehl...');
    }
  }
  if (!is_dir('./upload')) {
    if (!mkdir('./upload', 0664, true)) {
      error_log('Erstellung der Verzeichnisse schlug fehl...');
      die('Erstellung der Verzeichnisse schlug fehl...');
    }
  }

  //rename('./config/new_config.php', './config/config.php');
  if (file_exists('./config/config.php')) {
    rename('./config/config.php', './config/config.php_'.time());
  }
  file_put_contents('./config/config.php', '<?php  $config =' . var_export($new_config, true) . ';');
  chmod('./config/config.php', 0664);
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
  function FriendlyErrorType(int $type)
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
    <tr>
      <td>
        <input type="radio" name="error_reporting" value="'.$other_error.'" checked>
        '.$other_error.' (current value)
      </td>
    </tr>
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


require 'head.php';?>
    <div style=font-size:larger>
      <H1>Installation</H1>
      <p>Bitte ergänzen Sie die folgenden Werte um den Dienstplan zu konfigurieren.</p>
      <form class="" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <pre><?php //echo "$datalist_locales";?></pre>
        <table width=10%>
          <th colspan="99">
            Database settings
            <p class="hint">
              The installation script will create a new MySQL database.
              <br>
              All the information about the duty rosters will be stored password protected in this database.
            </p>
          </th>
          <tr>
            <td width=1%>Application name
            </td>
            <td width=1%><input type="text" name="application_name" value="<?php echo $config['application_name']?>">
            </td>
          </tr>
          <tr>
            <td>Database name
            </td>
            <td><input type="text" name="database_name" value="<?php echo $config['database_name']?>">
            </td>
          </tr>
          <tr>
            <td>Database User
            </td>
            <td><input type="text" name="database_user" value="<?php echo $config['database_user']?>">
            </td>
          </tr>
          <tr>
            <td>User Password
            </td>
            <td><input type="password" name="database_password" id="first_pass"
              onchange="compare_passwords()"
              onkeyup="compare_passwords()"
              onkeydown="compare_passwords()"
              onclick="compare_passwords()"
              onblur="compare_passwords()"
              onpaste="compare_passwords()"
              >
            </td>
            <td width=90%>
              <img id="approve_pass_img"    style="display:none" src="img/approve.png" height="20em">
              <img id="disapprove_pass_img" style="display:none" src="img/disapprove.png" height="20em">
              <!--
              <input type="text" id=clear_pass>
          -->
            </td>
          </tr>
          <tr>
            <td>Repeat Password
            </td>
            <td><input type="password" id="second_pass"
                onchange="compare_passwords()"
                onkeyup="compare_passwords()"
                onkeydown="compare_passwords()"
                onclick="compare_passwords()"
                onblur="compare_passwords()"
                onpaste="compare_passwords()"
                >
            </td>
          </tr>
          <th colspan="99">
            Contact information
            <p class="hint">
              Viewing users will be invited to address wishes and suggestions to the editor of the duty rosters.
            </p>
          </th>
          <tr>
            <td>Email
            </td>
            <td><input type="email" name="contact_email" value="<?php echo $config['contact_email']?>">
            </td>
          </tr>
          <th colspan="99">
            Technical details
            <p class="hint">
              Time values can be adapted to various local user's environments.
              <br>
              They depend on language and cultural conventions.
            </p>
          </th>
          <tr>
            <td>Locale
            </td>
            <td><input list="locales" value="<?php echo $config['LC_TIME']?>" name="LC_TIME">
              <?php echo "$datalist_locales"; ?>
            </td>
          </tr>
          <tr>
            <td>Charset
            </td>
            <td><input list="encodings" value="<?php echo $config['mb_internal_encoding']?>" name="mb_internal_encoding">
              <?php echo "$datalist_encodings"; ?>
            </td>
          </tr>
          <th colspan="99"> Debugging
              <p class="hint"> Which type of errors should be reported to the user?</p>
          </th>
          <tr>
            <td>
              <input type="radio" name="error_reporting" value="<?php echo "$error_error\" $error_error_checked";?>>
              Only fatal errors
            </td>
          </tr>
          <tr>
            <td>
              <input type="radio" name="error_reporting" value="<?php echo "$error_warning\" $error_warning_checked";?>>
              Also warnings
            </td>
          </tr>
          <tr>
            <td>
              <input type="radio" name="error_reporting" value="<?php echo "$error_notice\" $error_notice_checked";?>>
              And notices
            </td>
          </tr>
          <tr>
            <td>
              <input type="radio" name="error_reporting" value="<?php echo "$error_all\" $error_all_checked";?>>
              Everything
            </td>
          </tr>
          <?php if (!empty($other_error_html)) {
            echo "$other_error_html";
          }?>
          <th colspan="99">Approval
              <p class="hint">
                After a duty roster is planned, it has to be approved, before it is in effect.
                <br>
                Should viewers be able to see duty rosters before they are finally approved?
               </p>
          </th>
          <tr>
            <td><input type="radio" name="hide_disapproved" value="true">Hide
            </td>
          </tr>
          <tr>
            <td><input type="radio" name="hide_disapproved" value="false" checked>Show
            </td>
          </tr>
          <!--
          <tr>
            <td>
            </td>
            <td><input type="text">
            </td>
          </tr>
          <tr>
            <td>
            </td>
            <td><input type="text">
            </td>
          </tr>-->
        </table>
        <input type="submit">
      </form>
    </div>
  </body>
</html>
