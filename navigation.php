    <div class="head no-print">
        <ul>
            <li><a href=woche-out.php>Wochenansicht</a></li>
            <li><a href=tag-out.php>Tagesansicht</a></li>
            <li><a href=mitarbeiter-out.php>Personenansicht</a></li>
            <li><a href=stunden-out.php>Überstunden</a></li>
            <li><a href=abwesenheit-out.php title="Urlaub, Krankheit, Abwesenheit">Abwesenheit</a></li>
            <li style=float:right>
                <a onclick="toggle_show_administration()">
                    <?php echo $user; ?>&nbsp;
                    <img src=images/settings.png class="inline-image" alt="settings-button" title="Show settings">
                </a></li>
        </ul>
        <?php require 'administration-in.php'; ?>
    </div>
<?php
//This file is part of nearly every page. But DO NOT include it inside head.php! It is not part and should not be part of e.g. install.php!


//In the following lines we will define buttons for the use in other documents.

$rückwärts_button_img='
		<button type="submit" class="btn-primary no-print" value="" name="submitRückwärts">
			<i class="icon-black">
				<img src=images/backward.png class=button-image alt="Einen Tag rückwärts">
			</i>
			<br>
			1 Tag Rückwärts
		</button>';
$vorwärts_button_img='
		<button type="submit" class="btn-primary no-print" value="" name="submitVorwärts">
			<i class="icon-black">
				<img src=images/foreward.png class=button-image alt="Einen Tag vorwärts">
			</i>
			<br>
			1 Tag Vorwärts
		</button>';
$rückwärts_button_week_img='
		<button type="submit" class="btn-primary no-print" value="" name="submitWocheRückwärts">
			<i class="icon-black">
				<img src=images/backward.png class=button-image alt="Eine Woche rückwärts">
			</i>
			<br>
			1 Woche Rückwärts
		</button>';
$vorwärts_button_week_img='
		<button type="submit" class="btn-primary no-print" value="" name="submitWocheVorwärts">
			<i class="icon-black">
				<img src=images/foreward.png class=button-image alt="Eine Woche Vorwärts">
			</i>
			<br>
			1 Woche Vorwärts
		</button>';
$submit_button_img='
		<button type="submit" id=submit_button_img class="btn-primary btn-save no-print" value=Absenden name="submitDienstplan">
		  <i class="icon-white">
				<img src=images/save.png class=button-image alt="Speichern">
			</i>
			<br>
			Speichern
		</button>';
// TODO: The button should be inactive when the approval already was done.
$submit_approval_button_img='
		<button type="submit" class="btn-secondary no-print" value=Genehmigen name="submit_approval">
			<i class="icon-grey">
				<img src=images/approve.png class=button-image alt="Genehmigen">
			</i>
			<br>
			Genehmigen
		</button>';
//		<input type=submit value=Genehmigen name='submit_approval'>\n";
$submit_disapproval_button_img='
		<button type="submit" class="btn-secondary no-print" value=Ablehnen name="submit_disapproval">
			<i class="icon-grey">
				<img src=images/disapprove.png class=button-image alt="Ablehnen">
			</i>
			<br>
			Ablehnen
		</button>';
//	<input type=submit value=Ablehnen name='submit_disapproval'>\n";




		 ?>
