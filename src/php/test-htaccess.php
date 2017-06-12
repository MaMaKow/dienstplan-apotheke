<?php

require_once '../../default.php';
echo test_folders(array("/tmp/", "/config/", "/upload/", "/tests/"));

/*
 * Call the folder via http:// to see if it is visible
 * 
 * @param string $folder the name and position of the folder to call.
 * @return bool | string TRUE for blocked folders, an error message for visible folders.
 */

function secret_folder_is_secure($folder = "/tmp/") {
    $dirname = dirname($_SERVER["PHP_SELF"]);
    //This script lies within /src/php/ so therefore we have to move up by two levels:
    $dir_above1 = substr($dirname, 0, strrpos($dirname, "/"));
    $dir_above2 = substr($dirname, 0, strrpos($dir_above1, "/"));
    $filename = "http://" . $_SERVER["HTTP_HOST"] . $dir_above2 . $folder;

    list($response, $Response_remains) = get_headers($filename);
    $response_code = substr($response, strpos($response, " "), (strrpos($response, " ") - strpos($response, " ")));
    if (200 == $response_code) {
        $error_message = "Warning! The directory <a href='$filename'>$filename</a> seems to be world visible. Please make sure that the directory is not accessible by the public!";
        return $error_message;
    } elseif (403 == $response_code) {
        return TRUE;
    } elseif (404 == $response_code) {
        //TODO: This is an error. Some directory is missing. But we do not have any place to report it.
        return TRUE;
    } else {
        $error_message = "Error! The result could not be interpreted for the directory $filename. The server returned: '$response'. Please make sure that the directory is not accessible by the public!<br>";
        foreach ($Response_remains as $key => $response_http) {
            $error_message = $key . ": " . $response_http . "<br>\n";
                    
        }
        return $error_message;
    }
}

/*
 * Public execution function for secret_folder_is_secure
 */

function test_folders($Folders) {
    $error_message_html = "<div class=error_container>\n";
    foreach ((array) $Folders as $folder) {
        $secure = secret_folder_is_secure($folder);
        if (TRUE !== $secure) {
            $error_message_html .= build_error_message($secure);
            $error_message_exists = TRUE;
        }
    }
    $error_message_html .= "</div>\n";
    if ($error_message_exists) {
        require_once get_root_folder() . 'head.php';
        return $error_message_html;
    } else {
        return FALSE;
    }
}

function build_error_message($error_message_text) {
    $error_message_html = "\t<div class=warningmsg>" . $error_message_text . "</div>\n";
    return $error_message_html;
}

?>
