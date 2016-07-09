<!DOCTYPE html>
<?php
// TODO: I want a reminder inside the woche-in.php and tag-in.php (maybe within post-auswertung) if the last update is older then 3 months.
	require "default.php";
?>
<html>
<?php require 'head.php';?>
	<body>

		<?php
			require 'navigation.php';
			echo "<p style=height:5em></p>";
			if(isset($_POST["submit"]))
			{
				define ('SITE_ROOT', realpath(dirname(__FILE__)));

				$target_dir = "/upload/";
				$target_file = SITE_ROOT . $target_dir . basename($_FILES["fileToUpload"]["name"]);
				$upload_ok = 1;
				$file_type = pathinfo($target_file, PATHINFO_EXTENSION);

				if (file_exists($target_file))
				{
				// Check if file already exists
					$Fehlermeldung[]="Sorry, file already exists.";
					$upload_ok = 0;
				}
				elseif($file_type != "asy" )
				{
				// Allow certain file formats
					$Fehlermeldung[]="Sorry, only ASYS PEP files are allowed.";
					$upload_ok = 0;
				}
				elseif ($upload_ok == 0)
				{
				// Check if $upload_ok is set to 0 by an error
					$Fehlermeldung[]="Sorry, your file was not uploaded.";
					// if everything is ok, try to upload file
				}
				else
				{
					if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file))
					{
			        		$Warnmeldung[]="The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
			        		$Warnmeldung[]="It will be processed in the background.";
						$command="./pep.sh ".$config['database_user']." ".$config['database_password']." ".$config['database_name']." >> tmp/pep.log &";
						exec($command);
				    	}
					else
					{
					        $Fehlermeldung[]="Sorry, there was an error uploading your file.<br>\n";
				    	}
				}
			}
			//Hier beginnt die Fehlerausgabe. Es werden alle Fehler angezeigt, die wir in $Fehlermeldung gesammelt haben.
			if (isset($Fehlermeldung))
			{
				echo "<div class=errormsg><H1>\n";
				foreach($Fehlermeldung as $fehler)
				{
					echo "\t\t\t<H1>".$fehler."</H1>\n";
				}
				echo "</div>";
			}
			if (isset($Warnmeldung))
			{
				echo "<div class=warningmsg><H1>\n";
				foreach($Warnmeldung as $warnung)
				{
					echo "\t\t\t<H1>".$warnung."</H1>\n";
				}
				echo "\t\t</div>";
			}
		?>
		<div class=no-image>
			<form action="upload-in.php" method="post" enctype="multipart/form-data">
				Eine PEP-Datei zum Hochladen auswählen:<br>
				<input type="file" name="fileToUpload" id="fileToUpload"><br>
				<input type="submit" value="Upload" name="submit"><br>
			</form>
		</div>
		<?php require 'contact-form.php';?>

	</body>
</html>
