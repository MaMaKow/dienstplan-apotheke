<div class="foot no_print">
    <p><a href=#bottom onclick=unhide_contact_form()><?= gettext("Wishes, criticism, suggestions") ?>&nbsp;+</a></p>
    <?= user_dialog::build_contact_form(); ?>
    <?php user_dialog::contact_form_send_mail($workforce); ?>
    <a target="_blank" href="https://github.com/MaMaKow/dienstplan-apotheke/issues/new">
        <p>
            <?= gettext("Report a bug") ?>
        </p>
    </a>
    <p><!--Space between the contact links and the bottom--></p>
</div>
<div id='bottom'></div>
