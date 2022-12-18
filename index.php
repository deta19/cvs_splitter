<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>csv anallize</title>
</head>
<body>
	<?php

	if( isset($_POST["generate"]) ) {
	
		$target_file = $_POST['csv_file_uploaded'];

		if( file_exists($target_file)) {
			$uploaded_file = fopen($target_file, 'r' );

			$zip = new ZipArchive();
			$zip_name = "files" .time(). ".zip"; // Zip name
			$zip->open($zip_name,  ZipArchive::CREATE);

			//get file data text
			while (!feof($uploaded_file) ) {
			    $lines[] = fgetcsv($uploaded_file);

			}

			fclose( $uploaded_file );

			//create files
			$filter_collumns = array(); // data maybe from MySQL to add to your CSV file
			foreach ($_POST['header_csv'] as $key => $h_csv) {
				$temp_directory = 'temp_files/';
				
				// add your data to the CSV file
				foreach($lines as $k => $d) {
					if (  $k > 0 ) { // ignore the table header
						if( !empty( $d[$h_csv]  ) && !in_array($d[$h_csv], $filter_collumns[$h_csv]) ) {
							$filter_collumns[$h_csv][] = $d[$h_csv];
						}

					}
				}

				
			}

			//crreate new files
			foreach($filter_collumns as $ky => $filter_collumn) {

				for( $i = 0; $i < count($filter_collumn); $i++ ) {

					$file_name = utf8_encode(trim($filter_collumns[$ky][$i] ));
					$file_name = str_replace( " ", "", $file_name );
					$file_name = str_replace( "(", "", $file_name );
					$file_name = str_replace( ")", "", $file_name );
					$file_name = str_replace( "/", "", $file_name );
					$file_name = str_replace( "-", "", $file_name );
					$file_name = str_replace( ":", "", $file_name );
					$file_name = str_replace( ".", "", $file_name );
					$file_name = str_replace( ",", "", $file_name );
					// $file_name = str_replace( chr(0x96), "", $file_name );


					$new_file[$ky] = 'file_' . $file_name .'.csv';
					$temp_file = fopen($temp_directory . $new_file[$ky], 'w');

					fputcsv($temp_file, $lines[0]);


					foreach( $lines as $k => $d ) {

						if(  $k > 0 ) {

							if( !empty( $d[$ky]  ) && $d[$ky] == $filter_collumns[$ky][$i] ) {

								fprintf($temp_file, chr(0xEF).chr(0xBB).chr(0xBF));
								fputcsv($temp_file, $d );

							}
						}

					}

					fclose( $temp_file);

				 // add this file to the ZIP folder
				// echo $path = "temp_files/".$new_file[$ky];
					if(file_exists($path)){
						$zip->addFromString(basename($path),  file_get_contents($path));  
					}
					else{
						echo"file does not exist";
					}



  				// now delete this CSV file
				if(is_file($temp_directory . $new_file[$i])) {
					unlink($temp_directory . $new_file[$i]);
				}

				}
			}
			$zip->close();


			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename='.$zip_name);
			header('Content-Length: ' . filesize($zip));
			readfile($zip_name);

		}
	}

if( !empty( $_FILES ) ) {
	$target_dir = "files/";
	$original_name = basename($_FILES["csv"]["name"]);
	$file_type = substr( $_FILES["csv"]["type"], strpos($_FILES["csv"]["type"], '/') + 1 );
	$new_name = substr( $original_name, 0, strpos($original_name, '.') ) . md5( date('Y-m-d h:m:s') ) . '.' . $file_type;
	$size = $_FILES["csv"]['size'];

	$target_file = $target_dir . $new_name;
	$uploadOk = 1;
	$file = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

	$tmp_name = $_FILES["csv"]["tmp_name"];
	$name = basename($_FILES["csv"]["name"]);
	move_uploaded_file($tmp_name, "$target_file");


	if( isset($_POST["submitfile"]) ) {
	
		if( file_exists($target_file)) {
			$uploaded_file = fopen($target_file, 'r' );
			$get_file_data = fgetcsv($uploaded_file, $size, ',');

			?>
		<div class="wrapper">
			<form action="" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="csv_file_uploaded" value="<?php echo $target_file; ?>">
				<ul class="csv_header_elems">
					<?php
					foreach ($get_file_data as $key => $csv_head) {
					?>
						<li class="item">
							<label><input type="checkbox" name="header_csv[]" value="<?php echo $key;?>"><?php echo $csv_head;?></label>
						</li>
					<?php
					}
					?>
				</ul>
				<input type="submit" value="Generate files" name="generate">
			</form>

		</div>
		<?php
			fclose( $uploaded_file );
		} else {
			?>
			<div class="warning">File not uploaded corectly on server</div>
			<?php
		}
	}

} else {


	?>

	<form action="" method="POST" enctype="multipart/form-data">
		<input type="file" name="csv" id="fileToUpload">
  		<input type="submit" value="Upload file" name="submitfile">
	</form>
<?php
}
?>
</body>
</html>