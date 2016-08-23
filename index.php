
<html>
	<?php
		if (!file_exists('./config/config.php')) {
			echo "The application does not seem to be configured. Please see the <a href=install.php>installation page</a>";
		}
		require 'default.php';
		require 'head.php';
	?>
	<body>
		<?php require 'navigation.php';?>

		<p>Dies wird ein Navigationsmenü für die Dienstplanung</p>
		<H1>
		<table border=0>
			<tr>
				<td>
					<a href=woche-out.php>Wochenansicht</a>
				</td>
			</tr>
			<tr>
				<td>
					<a href=tag-out.php>Tagesansicht</a>
				</td>
			</tr>
			<tr>
				<td>
					<a href=mitarbeiter-out.php>Personenansicht</a>
				</td>
			</tr>
			<tr>
				<td>
					<a href=stunden-out.php>Überstunden</a>
				</td>
			</tr>
			<tr>
				<td>
					<a href=abwesenheit-out.php>Urlaub, Krankheit, Abwesenheit</a>
				</td>
			</tr>
		</table>
		</H1>
		<?php require 'contact-form.php';?>
	</body>
</html>
