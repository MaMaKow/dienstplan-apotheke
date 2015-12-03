<?php //Hier entsteht die Wochenauswahl.
$heute=date('Y-m-d')
?>
<html>
	<head>
	</head>
	<body>
		<form method=POST action=woche-in.php>
			<p>Welche Woche soll bearbeitet werden?</p>
			<input name=woche type=date value=<?php echo $heute; ?>>
			<input type="submit" name="wochenAuswahl" value="Anzeigen" />
		</form>
	</body>
</html>
