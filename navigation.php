<script>
	function toggle_show_administration ()
	{
		var admin_div_id = document.getElementById('administration');
		if (admin_div_id.style.display == "block")
		{
				admin_div_id.style.display = "none";
		}
		else
		{
				admin_div_id.style.display = "block";
		}

	}
</script>
<noscript>Sorry, your browser does not support JavaScript!</noscript>
		<div class=head>
			<ul class=no-print>
				<li><a href=woche-out.php>Wochenansicht</a></li>
				<li><a href=tag-out.php>Tagesansicht</a></li>
				<li><a href=mitarbeiter-out.php>Personenansicht</a></li>
				<li><a href=stunden-out.php>Überstunden</a></li>
				<li><a href=abwesenheit-out.php title="Urlaub, Krankheit, Abwesenheit">Abwesenheit</a></li>
				<div style=float:right><li><a onclick="toggle_show_administration()"><?php if(isset($_SERVER['REMOTE_USER'])){echo $_SERVER['REMOTE_USER'];}else{echo "Nicht eingeloggt";} ?>&nbsp<img src=images/settings.png height=15em></a></li></div>
			</ul>
			<?php require 'administration-in.php';?>
		</div>
