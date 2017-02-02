<div class="foot no-print">
	<script>
		function unhideContactForm()
		{
			document.getElementById("contactForm").style.display = "inline";
		}
	</script>
	<p><a href=#bottom onclick=unhideContactForm()>Wünsche, Kritik, Anregungen&nbsp;+</a></p>
	<form id=contactForm style=display:none method=POST>
		<table>
			<tr><td>Absender</td><td><input style=width:320px type="text" name=VK value=""></td></tr>
			<tr><td>Nachricht</td><td><textarea style=width:320px name=nachricht rows=5></textarea></td><tr>
			<tr><td>Kontaktadresse</td><td><input style=width:320px type="email" name=email value=""></td></tr>
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
	if( isset($_POST['VK']) )
	{
		$nachricht.= "Die Nachricht stammt von:";
		$nachricht.= $_POST['VK'];
		$nachricht.= "\n\n";
	}
	if( isset($_POST['nachricht']) )
	{
		$nachricht.= "<<<Nachricht<<<\n";
		$nachricht.= $_POST['nachricht'];
		$nachricht.= "\n";
		$nachricht.= ">>>   >>>\n";
		$nachricht.= "\n\n";
	}
	if( isset($_POST['dienstplan']) )
	{
		$nachricht.= "<<<Dienstplan<<<\n";
		$nachricht.= $_POST['dienstplan'];
		$nachricht.= "\n";
		$nachricht.= ">>>   >>>";
		$nachricht.= "\n\n";
	}
	$header = 'From: '.$config['contact_email']."\r\n" ;
	if( isset($_POST['email'])  )
	{
		$header.= 'Reply-To: '.$_POST['email'] . "\r\n" ;
	}
	$header.= 'X-Mailer: PHP/' . phpversion();
	if(isset($_POST['submitContactForm']))
	{
		$versendet=mail($empfaenger, $betreff, $nachricht, $header);
		if ($versendet)
		{
			echo "Die Nachricht wurde versendet. Vielen Dank!";
		}
		else
		{
			echo "Fehler beim Versendet der Nachricht. Das tut mir Leid.";
		}
	}
?>
<a target="_blank" href="https://github.com/MaMaKow/dienstplan-apotheke/issues/new">Einen Programmfehler melden</a>
</div>
<a name=bottom></a>
