<!DOCTYPE html>
<?php
	require "default.php";
        require 'head.php';
        require 'navigation.php';
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
			        		$Message[]="The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
			        		$Message[]="It will be processed in the background.";
                                                echo "<input hidden type=text id=filename value=upload/".$_FILES["fileToUpload"]["name"].">\n";
				    	}
					else
					{
					        $Fehlermeldung[]="Sorry, there was an error uploading your file.<br>\n";
				    	}
				}
			}


		?>
		<p style=height:2em></p>
		<div id=main-area>
			<form action="upload-in.php" method="post" enctype="multipart/form-data">
				Eine PEP-Datei zum Hochladen auswählen:<br>
                                <input type="file" name="fileToUpload" id="fileToUpload" onchange="reset_update_pep()"><br>
				<input type="submit" value="Upload" name="submit"><br>
			</form>
		</div>
            <?php
            //Hier beginnt die Fehlerausgabe. Es werden alle Fehler angezeigt, die wir in $Fehlermeldung gesammelt haben.
		if (isset($Fehlermeldung))
		{
                    echo "<div class=errormsg>\n";
                    foreach($Fehlermeldung as $fehler)
                    {
                        echo "\t\t\t<H1>".$fehler."</H1>\n";
                    }
			echo "</div>";
		}
		echo "<div class=warningmsg>\n";
                if (isset($Message))
                {
                    echo '<div id=phpscriptmessages>';
                    foreach($Message as $message)
                    {
			echo "\t\t\t<p>".$message."</p>\n";
                    }
                    echo '</div>';
		}
                echo "\t\t\t<p id=xmlhttpresult></p>\n";
                echo "\t\t\t<p id=javascriptmessage></p>\n";
		echo "\t\t</div>";
                require 'contact-form.php';
           ?>
            <script type="text/javascript">
                update_pep();
            </script>
            <!-- The following lines might be an alternative to using javascript with ajax.
           
            function do_post_request($url, $data, $optional_headers = null,$getresponse = false) {
      $params = array('http' => array(
                   'method' => 'POST',
                   'content' => $data
                ));
      if ($optional_headers !== null) {
         $params['http']['header'] = $optional_headers;
      }
      $ctx = stream_context_create($params);
      $fp = @fopen($url, 'rb', false, $ctx);
      if (!$fp) {
        return false;
      }
      if ($getresponse){
        $response = stream_get_contents($fp);
        return $response;
      }
    return true;
}-->
	</body>
</html>
