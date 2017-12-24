<div class="foot no-print">
    <script>
        /**
         * Displays the element "contactForm".
         *
         * @returns void
         */
        function unhideContactForm()
        {
            document.getElementById("contactForm").style.display = "inline";
        }
    </script>
    <p><a href=#bottom onclick=unhideContactForm()><?= gettext("Wishes, criticism, suggestions") ?>&nbsp;+</a></p>
    <form id=contactForm style=display:none method=POST>
        <table>
            <tr><td><?= gettext("Message") ?></td><td><textarea style=width:320px name=message rows=5></textarea></td></tr>
        </table>
        <input type="hidden" name=dienstplan value="<?php var_export($Dienstplan) ?>">
        <input type="submit" name=submitContactForm value="Absenden">
        <p><!--Nur damit der Submit-Button nicht ganz am unteren Seitenrand klebt.-->
    </form>
    <?php
    $recipient = $config['contact_email'];
    $subject = "PDR " . $config['application_name'] . " " . gettext('has a comment');
    $message = "";
    $trace = debug_backtrace();
    $message .= $trace[0]['file'];
    $message .= "\n\n";
    if (filter_has_var(INPUT_POST, 'VK')) {
        $message .= "Die Nachricht stammt von:";
        $message .= $List_of_employee_full_names[$_SESSION['user_employee_id']];
        $message .= "\n\n";
    }
    if (filter_has_var(INPUT_POST, 'message')) {
        $message .= "<<<Nachricht<<<\n";
        $message .= filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
        $message .= "\n";
        $message .= ">>>   >>>\n";
        $message .= "\n\n";
    }
    if (filter_has_var(INPUT_POST, 'dienstplan')) {
        $message .= "<<<Dienstplan<<<\n";
        $message .= filter_input(INPUT_POST, 'dienstplan', FILTER_SANITIZE_STRING);
        $message .= "\n";
        $message .= ">>>   >>>";
        $message .= "\n\n";
    }
    if (!empty($_SESSION['user_email'])) {
        $header = 'From: ' . $_SESSION['user_email'] . "\r\n";
        $header .= 'Reply-To: ' . $_SESSION['user_email'] . "\r\n";
    } else {
        $header = 'From: ' . $config['contact_email'] . "\r\n";
    }
    $header .= 'X-Mailer: PHP/' . phpversion();
    if (filter_has_var(INPUT_POST, 'submitContactForm')) {
        $versendet = mail($recipient, $subject, $message, $header);
        if ($versendet) {
            echo "Die Nachricht wurde versendet. Vielen Dank!";
        } else {
            error_log(var_export(error_get_last(), TRUE));
            echo "Fehler beim Versenden der Nachricht. Das tut mir Leid.";
        }
    }
    ?>
    <a target="_blank" href="https://github.com/MaMaKow/dienstplan-apotheke/issues/new">
        <p>
            <?= gettext("Report a bug") ?>
        </p>
    </a>
    <p><!--Space between the contact links and the bottom--></p>
</div>
<div id='bottom'></div>
