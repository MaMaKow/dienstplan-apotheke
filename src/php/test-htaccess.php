<?php
require_once '../../default.php';
echo test_folders(array("/tmp/", "/tmp/", "/tmp/"));

function secret_folder_is_secure($folder = "/tmp/") {
    $dirname = dirname($_SERVER["PHP_SELF"]);
    //This script lies within /src/php/ so therefore we have to move up by two levels:
    $dir_above1 = substr($dirname, 0, strrpos($dirname, "/"));
    $dir_above2 = substr($dirname, 0, strrpos($dir_above1, "/"));
    $filename = "http://" . $_SERVER["HTTP_HOST"] . $dir_above2 . $folder;

    list($response) = get_headers($filename);
    $response_code = substr($response, strpos($response, " "), (strrpos($response, " ") - strpos($response, " ")));
    if (200 == $response_code) {
        $error_message = "Error! The directory $filename is world visible. Please make sure that the directory is not accessible by the public!";
        return $error_message;
    } elseif (403 == $response_code) {
        return TRUE;
    } else {
        $error_message = "Error! We could not interpret the result. The server returned: '$response'. Please make sure that the directory is not accessible by the public!";
        return $error_message;
    }
}

function test_folders($Folders) {
    $error_message_html = "<div class=error_container>\n";
    foreach ($Folders as $folder){
        $secure = secret_folder_is_secure($folder);
        if (TRUE !== $secure){
            $error_message_html .= build_error_message($secure);
            $error_message_exists = TRUE;
        }
    }
    $error_message_html .= "</div>\n";
    if($error_message_exists){
        require_once get_root_folder() . 'head.php';
        return $error_message_html;
    } else {
        return FALSE;    
        echo "Alles ist gut.";
    }
}

function build_error_message($error_message_text) {
    $error_message_html = "<p class=errormsg>" . $error_message_text . "</p>\n";
    return $error_message_html;
}
?>
