<?php
require 'funktionen.php';
//Define different error reporting options:
$error_error   = "E_ERROR | E_USER_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_PARSE";
$error_warning = "$error_error   | E_WARNING | E_USER_WARNING | E_CORE_WARNING | E_COMPILE_WARNING";
$error_notice  = "$error_warning | E_NOTICE | E_USER_NOTICE | E_DEPRECATED | E_USER_DEPRECATED";
$error_all     = "$error_notice  | E_STRICT";

//We might want to read some kind of standard values from a file:
include "config/default_config.php";
$default_config = $config;

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

//Read the POST values:
foreach ($_POST as $key => $value) {
  if (!empty($_POST[$key])) {
      $new_config[$key] = sanitize_user_input($value);
  } elseif (isset($default_config[$key])) {
      $new_config[$key] = sanitize_user_input($default_config[$key]);
  }
}

//echo "<pre>"; var_export($config); echo "</pre>";
echo "<pre>"; var_export($new_config); echo "</pre>";
file_put_contents('config/new_config.php', '<?php  $config =' . var_export($new_config, true) . ';');
?>



<html>
<?php require 'head.php';?>
  <body>
    <div style=font-size:larger>
      <H1>Installation</H1>
      <p>Bitte ergänzen Sie die folgenden Werte um den Dienstplan zu konfigurieren.</p>
      <form class="" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <pre><?php //echo "$datalist_locales";?></pre>
        <table>
          <th colspan="3">
            Database settings
            <p class="hint">
              The installation script will create a new MySQL database.
              <br>
              All the information about the duty rosters will be stored password protected in this database.
            </p>
          </th>
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
            <td><input type="password" name="database_password" id="first_pass">
            </td>
            <td rowspan="2">
              <img src="images/approve.png" height="20em">
              <img src="images/disapprove.png" height="15em">
              <input type="text" id=clear_pass>
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
          <th colspan="3">
            Contact information
            <p class="hint">
              Viewing users will be invited to address wishes and suggestions to the editor of the duty rosters.
            </p>
          </th>
          <tr>
            <td>Email
            </td>
            <td><input type="email" name="contact_email">
            </td>
          </tr>
          <th colspan="3">
            Technical details
            <p class="hint">
              Time values can be adapted to various local user's environments.
              <br>
              They depend on language and cultural conventions.
            </p>
          </th>
          <tr>
            <td>Charset
            </td>
            <td><input list="encodings" value="<?php echo $config['mb_internal_encoding']?>" name="mb_internal_encoding">
              <?php echo "$datalist_encodings"; ?>
            </td>
          </tr>
          <tr>
            <td>Locale
            </td>
            <td><input list="locales" value="<?php echo $config['LC_TIME']?>" name="LC_TIME">
              <?php echo "$datalist_locales"; ?>
            </td>
          </tr>
          <th colspan="3"> Debugging
              <p class="hint"> Which type of errors should be reported to the user?</p>
          </th>
          <tr>
            <td>
              <input type="radio" name="error_reporting" value="<?php echo $error_error?>">
              Only fatal errors
            </td>
          </tr>
          <tr>
            <td>
              <input type="radio" name="error_reporting" value="<?php echo $error_warning?>">
              Also warnings
            </td>
          </tr>
          <tr>
            <td>
              <input type="radio" name="error_reporting" value="<?php echo $error_notice?>">
              And notices
            </td>
          </tr>
          <tr>
            <td>
              <input type="radio" name="error_reporting" value="<?php echo $error_all?>" checked>
              Everything
            </td>
          </tr>
          <th colspan="3">Approval
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
          </tr>
        </table>
        <input type="submit">
      </form>
    </div>
  </body>
</html>
