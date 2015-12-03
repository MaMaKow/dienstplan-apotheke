<?php
function schreiben_tabelle ($Dienstplan)
{
global $Mitarbeiter;
echo "\t\t\t\t</tr><tr>\n";
foreach($Dienstplan as $key => $Dienstplantag)
{
	if(isset($Dienstplantag['VK']))
	{
	$PlanAnzahl[]=(count($Dienstplantag['VK']));
	}
	else
	{
	$PlanAnzahl[]=0;
	}
} 
$planAnzahl=max($PlanAnzahl); //Die Anzahl der Zeilen der Tabelle richtet sich nach dem Tag mit den meisten Einträgen.
for ($j=0; $j<$planAnzahl; $j++)
{
	if(isset($feiertag) && !isset($notdienst)){break 1;}
	echo "\t\t\t\t</tr></thead><tr>\n";
	for ($i=0; $i<count($Dienstplan); $i++)
	{//Mitarbeiter
		$zeile="";
		echo "\t\t\t\t\t<td align=left>";
		$zeile.="<b>";
		if (isset($Dienstplan[$i]["VK"][$j]) && isset($Mitarbeiter[$Dienstplan[$i]["VK"][$j]]) )
		{ 
//			$zeile.=$Dienstplan[$i]["VK"][$j]." ".$Mitarbeiter[$Dienstplan[$i]["VK"][$j]];
			$zeile.=$Mitarbeiter[$Dienstplan[$i]["VK"][$j]];
			$zeile.="</b>&nbsp/&nbsp";
			$zeile.=$Dienstplan[$i]["Stunden"][$j];
			$zeile.="&nbsph";
		}
		//Dienstbeginn
		$zeile.="&nbsp <br> ";
		if (isset($Dienstplan[$i]["VK"][$j])) 
		{
			$zeile.=strftime('%H:%M',strtotime($Dienstplan[$i]["Dienstbeginn"][$j]));
		}
		//Dienstende
		if (isset($Dienstplan[$i]["VK"][$j])) 
		{
			$zeile.=" - ";
			$zeile.=strftime('%H:%M',strtotime($Dienstplan[$i]["Dienstende"][$j]));
		}
		$zeile.="";
		echo $zeile;
		
//	Mittagspause
		$zeile="";
	echo "\t\t\t\t<br>\n";
		if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0 )
		{
			$zeile.=" Pause: ";
			$zeile.= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsbeginn"][$j]));
		}
		if (isset($Dienstplan[$i]["VK"][$j]) and $Dienstplan[$i]["Mittagsbeginn"][$j] > 0 )
		{
			$zeile.=" - ";
			$zeile.= strftime('%H:%M', strtotime($Dienstplan[$i]["Mittagsende"][$j]));
		}
		$zeile.="";
		
		echo $zeile;
		echo "</td>\n";
	}
}
echo "\t\t\t\t</tr>\n";
}
?>
