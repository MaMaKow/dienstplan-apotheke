		<div class=head>
			<ul class=no-print>
				<li><a href=woche-out.php>Wochenansicht</a></li>
				<li><a href=tag-out.php>Tagesansicht</a></li>
				<li><a href=mitarbeiter-out.php>Personenansicht</a></li>
				<li><a href=stunden-out.php>Überstunden</a></li>
				<li><a href=abwesenheit-out.php title="Urlaub, Krankheit, Abwesenheit">Abwesenheit</a></li>
				<div style=float:right><li><a href=administration-in.php><?php if(isset($_SERVER['REMOTE_USER'])){echo $_SERVER['REMOTE_USER'];}else{echo "Nicht eingeloggt";} ?></a></li></div>
			</ul>
		</div>
