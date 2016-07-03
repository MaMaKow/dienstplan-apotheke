		<div class=head>
			<ul class=no-print>
				<li><a href=woche-out.php>Wochenansicht</a></li>
				<li><a href=tag-out.php>Tagesansicht</a></li>
				<li><a href=mitarbeiter-out.php>Personenansicht</a></li>
				<li><a href=stunden-out.php>Überstunden</a></li>
				<li><a href=abwesenheit-out.php title="Urlaub, Krankheit, Abwesenheit">Abwesenheit</a></li>
				<div style=float:right><li><a onclick="toggle_show_administration()"><?php echo $user;?>&nbsp<img src=images/settings.png height=15em></a></li></div>
			</ul>
			<?php require 'administration-in.php';?>
		</div>
