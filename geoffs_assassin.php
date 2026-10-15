 
			<?php
			$this_file = __FILE__; 
			$main_script = "geoff.php";
			sleep(300);
			$handle = fopen("geoff_uploaded_files.txt", "r");
			if($handle){
				while (($line = fgets($handle)) !== false) {
					$line = trim($line);
					unlink($line);
				}
				fclose($handle);
				unlink("geoff_uploaded_files.txt");
			}			
			unlink($main_script);
			unlink($this_file);            
			?>
		