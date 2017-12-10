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
	<p><a href=#bottom onclick=unhideContactForm()><?=gettext("Wishes, criticism, suggestions")?>&nbsp;+</a></p>
	<form id=contactForm style=display:none method=POST>
		<table>
                    <tr><td><?=gettext("From")?></td><td><input style=width:320px type="text" name=VK value=""></td></tr>
			<tr><td><?=gettext("Message")?></td><td><textarea style=width:320px name=nachricht rows=5></textarea></td></tr>
			<tr><td><?=gettext("Email")?></td><td><input style=width:320px type="email" name=email value=""></td></tr>
		</table>
		<input type="hidden" name=dienstplan value="<?php var_export($Dienstplan)?>">
		<input type="submit" name=submitContactForm value="Absenden">
		<p><!--Nur damit der Submit-Button nicht ganz am unteren Seitenrand klebt.-->
	</form>
<?php
	$empfaenger = 'dienstplan@martin-mandelkow.de';
	$betreff = 'Dienstplan hat einen Kommentar';
	$nachricht = "";
	$trace = debug_backtrace();
	$nachricht.= $trace[0]['file'];
	$nachricht.= "\n\n";
	if( filter_has_var(INPUT_POST, 'VK') )
	{
		$nachricht.= "Die Nachricht stammt von:";
		$nachricht.= filter_input(INPUT_POST, 'VK', FILTER_SANITIZE_STRING);
		$nachricht.= "\n\n";
	}
	if( filter_has_var(INPUT_POST, 'nachricht') )
	{
		$nachricht.= "<<<Nachricht<<<\n";
		$nachricht.= filter_input(INPUT_POST, 'nachricht', FILTER_SANITIZE_STRING);
		$nachricht.= "\n";
		$nachricht.= ">>>   >>>\n";
		$nachricht.= "\n\n";
	}
	if( filter_has_var(INPUT_POST, 'dienstplan') )
	{
		$nachricht.= "<<<Dienstplan<<<\n";
		$nachricht.= filter_input(INPUT_POST, 'dienstplan', FILTER_SANITIZE_STRING);
		$nachricht.= "\n";
		$nachricht.= ">>>   >>>";
		$nachricht.= "\n\n";
	}
	$header = 'From: '.$config['contact_email']."\r\n" ;
	if( filter_has_var(INPUT_POST, 'email')  )
	{
		$header.= 'Reply-To: ' . filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING) . "\r\n" ;
	}
	$header.= 'X-Mailer: PHP/' . phpversion();
	if(filter_has_var(INPUT_POST, 'submitContactForm'))
	{
		$versendet=mail($empfaenger, $betreff, $nachricht, $header);
		if ($versendet)
		{
			echo "Die Nachricht wurde versendet. Vielen Dank!";
		}
		else
		{
			echo "Fehler beim Versenden der Nachricht. Das tut mir Leid.";
		}
	}
?>
<a target="_blank" href="https://github.com/MaMaKow/dienstplan-apotheke/issues/new"><?=gettext("Report a bug")?></a>
</div>
<div id='bottom'></div>
