<?php
/*Dieses Script soll eine Anwesenheitsliste vorbereiten.
Diese Liste wird dann ausgehängt und von den zuständigen Apothekern geführt.
Bekannte Urlaubszeiten, und sonstige Abwesenheiten sollten in der Tabelle aber bereits enthalten sein.*/

	require 'default.php';
	require 'db-verbindung.php';

	if (isset($_POST["month"])) {
	  $month=$_POST["month"];
	  } else {
	  $month=date("n");
	}
	$start_datum=mktime( 0, 0, 0, $month, 1 );
	$datum=$start_datum;
	//Die Mitarbeiterliste benötigt ein $datum. Denn Mitarbeiter sind nicht auf ewig bei uns.
	require 'db-lesen-mitarbeiter.php';
	$Months = array();
	for( $i = 1; $i <= 12; $i++ ) {
	    $Months[ $i ] = strftime( '%B', mktime( 0, 0, 0, $i, 1 ) );
	}

?>
<HTML>
  <HEAD>
    <META charset=UTF-8>
		<script type="text/javascript" src="javascript.js" ></script>
		<LINK rel="stylesheet" type="text/css" href="style.css" media="all">
		<LINK rel="stylesheet" type="text/css" href="print.css" media="print">
  </HEAD>
  <BODY>
<?php
	require 'navigation.php';
?>
    <FORM method=post class="no-print">
      <SELECT name=month onchange=this.form.submit()>
        <?php foreach ($Months as $month_number => $month_name) {
              echo "<option value=$month_number";
              if ($month_number==$month) {
                echo " SELECTED ";
              }
              echo ">$month_name</option>\n";
            } ?>
      <SELECT>
    </FORM>
    <TABLE border=1>
      <TR>
        <TD>Anwesenheit</TD>
        <?php foreach ($Mitarbeiter as $vk => $name) {
      echo '<TD>'.mb_substr($name, 0, 4)."<br>$vk</TD>";
  }?>
      </TR>
      <?php
        //$start_datum = strtotime('01.02.2016');
        for ($datum = $start_datum; $datum < strtotime('+ 1 month', $start_datum); $datum = strtotime('+ 1 day', $datum)) {
            if (date('N', $datum) >= 6) {
                echo '<TR class=wochenende><TD>'.strftime('%a', $datum).'</TD>';
                foreach ($Mitarbeiter as $vk => $name) {
                    echo '<TD></TD>';
                }
            } else {
                require 'db-lesen-abwesenheit.php';
                require 'db-lesen-notdienst.php';
                echo '<TR><TD>'.strftime('%a %d.%m.%Y', $datum).'</TD>';
                foreach ($Mitarbeiter as $vk => $name) {
                    if (isset($Abwesende) and array_search($vk, $Abwesende) !== false) {
                        if (preg_match('/Krank/i', $Abwesenheits_grund[$vk])) {
                            $grund_string = 'K';
                        } elseif (preg_match('/Kur/i', $Abwesenheits_grund[$vk])) {
                            $grund_string = 'K';
                        } elseif (preg_match('/Urlaub/i', $Abwesenheits_grund[$vk])) {
                            $grund_string = 'U';
                        } elseif (preg_match('/Elternzeit/i', $Abwesenheits_grund[$vk])) {
                            $grund_string = 'E';
                        } elseif (preg_match('/Nicht angestellt/i', $Abwesenheits_grund[$vk])) {
                            $grund_string = 'N/A';
                        }elseif (preg_match('/Notdienst/i', $Abwesenheits_grund[$vk])) {
                            $grund_string = 'NA';
                        } else {
                            $grund_string = mb_substr($Abwesenheits_grund[$vk], 0, 3);
                        }
                        echo '<TD title="'.$Abwesenheits_grund[$vk].'">'.$grund_string.'</TD>';
                    } elseif (isset($notdienst) and $notdienst['vk'] == $vk) {
                        echo '<TD>N</TD>';
                    } else {
                        echo '<TD></TD>';
                    }
                }
            }
            echo "</TR>\n";
        }?>

      </TD>
    </TABLE>
    Legende
    <TABLE>
      <TR><TD>K</TD><TD>Krank</TD><TD>U</TD><TD>Urlaub</TD><TD>E</TD><TD>Elternzeit</TD>
			<!--</TR><TR>!-->
				<TD>N/A</TD><TD>Nicht angestellt</TD><TD>N</TD><TD>Notdienst</TD><TD>NA</TD><TD>Ausgleich nach Notdienst</TD></TR>
    </TABLE>
		<?php require 'contact-form.php';?>
  </BODY>
</HTML>