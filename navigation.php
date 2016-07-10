		<div class="head no-print">
			<ul>
				<li><a href=woche-out.php>Wochenansicht</a></li>
				<li><a href=tag-out.php>Tagesansicht</a></li>
				<li><a href=mitarbeiter-out.php>Personenansicht</a></li>
				<li><a href=stunden-out.php>Überstunden</a></li>
				<li><a href=abwesenheit-out.php title="Urlaub, Krankheit, Abwesenheit">Abwesenheit</a></li>
				<div style=float:right><li><a onclick="toggle_show_administration()"><?php echo $user;?>&nbsp<img src=images/settings.png height=15em></a></li></div>
			</ul>
			<?php require 'administration-in.php';?>
		</div>
		<?php
		$rückwärts_button_img='<button type="submit" class="btn btn-primary no-print" value="" name="submitRückwärts">
		  <i class="icon-user icon-white"><img src=images/backward.png width=32px></i><br>1 Tag Rückwärts
		</button>';
		$vorwärts_button_img='<button type="submit" class="btn btn-primary no-print" value="" name="submitVorwärts">
		  <i class="icon-user icon-white"><img src=images/foreward.png width=32px></i><br>1 Tag Rückwärts
		</button>';
		$submit_button_img='<button type="submit" class="btn btn-primary no-print" value=Absenden name="submitDienstplan">
		  <i class="icon-user icon-white"><img src=images/save.png width=32px></i><br>Speichern
		</button>';
		// TODO: The button should be inactive when the approval already was done.
		$submit_approval_button="\t\t\t\t<input type=submit value=Genehmigen name='submit_approval'>\n";
		$submit_disapproval_button="\t\t\t\t<input type=submit value=Ablehnen name='submit_disapproval'>\n";




		 ?>
