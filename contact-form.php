<div class=foot>
	<script>
		function unhideContactForm()
		{
			document.getElementById("contactForm").style.display = "inline";
		}	
	</script>
	<p><a href=#bottom onclick=unhideContactForm()>Wünsche, Kritik, Anregungen&nbsp+</a></p>
	<form id=contactForm style=display:none method=POST>
		<table>
			<tr><td>Absender</td><td><input style=width:320px type="text" name=VK value=""></td></tr>
			<tr><td>Nachricht</td><td><textarea style=width:320px name=nachricht rows=5></textarea></td><tr>
			<tr><td>Kontaktadresse</td><td><input style=width:320px type="email" name=email value=""></td></tr>
		</table>
		<input type="hidden" name=dienstplan value="<?php var_export($Dienstplan)?>">
		<input type="submit" value="Absenden">
		<p><!--Nur damit der Submit-Button nicht ganz am unteren Seitenrand klebt.-->
	</form>
</div>
<?php
	if(isset($_POST['nachricht']))
	{
		$empfaenger = 'martin-kreimann@googlemail.com';
		$betreff = 'Dienstplan hat einen Kommentar';
		$nachricht = $_POST['nachricht'];
		$nachricht.= "\n";
		$nachricht.= $_POST['dienstplan'];
		$header = 'From: feedback@martin-mandelkow.de' . "\r\n" .
		    'Reply-To: refeedback@martin-mandelkow.de' . "\r\n" .
		    'X-Mailer: PHP/' . phpversion();
		
		$versendet=mail($empfaenger, $betreff, $nachricht, $header);
		echo "<div class=foot>";
		if ($versendet)
		{
			echo "Die Nachricht wurde versendet. Vielen Dank!";
		}
		else
		{
			echo "Fehler beim Versendet der Nachricht. Das tut mir Leid.";
		}
		echo "</div>";
	}
?>
<a name=bottom>
