<?php

if (session_start() and session_destroy()) {
    echo "Logout erfolgreich";
}
?>
