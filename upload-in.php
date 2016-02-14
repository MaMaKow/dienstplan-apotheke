<!DOCTYPE html>
<?php
	require "default.php";
?>
<html>
	<head>
		<meta charset=UTF-8>
		<link rel="stylesheet" type="text/css" href="style.css" media="all">
		<link rel="stylesheet" type="text/css" href="print.css" media="print">
	</head>
	<body>

		<?php
			if(isset($_POST["submit"]))
			{
				define ('SITE_ROOT', realpath(dirname(__FILE__)));

				$target_dir = "/upload/";
				$target_file = SITE_ROOT . $target_dir . basename($_FILES["fileToUpload"]["name"]);
				$uploadOk = 1;
				$fileType = pathinfo($target_file, PATHINFO_EXTENSION);

				if (file_exists($target_file)) 
				{
				// Check if file already exists
					$Fehlermeldung[]="Sorry, file already exists.";
					$uploadOk = 0;
				}
				elseif($fileType != "asy" )
				{
				// Allow certain file formats
					$Fehlermeldung[]="Sorry, only ASYS PEP files are allowed.";
					$uploadOk = 0;
				}
				elseif ($uploadOk == 0) 
				{
				// Check if $uploadOk is set to 0 by an error
					$Fehlermeldung[]="Sorry, your file was not uploaded.";
					// if everything is ok, try to upload file
				}
				else
				{
					if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) 
					{
			        		$Warnmeldung[]="The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
			        		$Warnmeldung[]="It will be processed in the background.";
						$command="./pep.sh ".$database_user." ".$database_password." ".$database_name." >> tmp/pep.log &";
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

		<form action="upload-in.php" method="post" enctype="multipart/form-data">
			Eine PEP-Datei zum Hochladen auswählen:<br>
			<input type="file" name="fileToUpload" id="fileToUpload"><br>
			<input type="submit" value="Upload" name="submit"><br>
		</form>
	</body>
</html>
