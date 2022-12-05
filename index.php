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
$zip_name = time().".zip"; // Zip name
$zip->open($zip_name,  ZipArchive::CREATE);




			while (!feof($uploaded_file) ) {
			    $lines[] = fgetcsv($uploaded_file);

			}
			// var_dump( $lines );
			
			fclose( $uploaded_file );

			var_dump( $_POST['header_csv'] );

			//create files
			$filter_collumns = array(); // data maybe from MySQL to add to your CSV file
			foreach ($_POST['header_csv'] as $key => $h_csv) {
				$temp_directory = 'temp_files/';
				

				// add your data to the CSV file
				foreach($lines as $d) {


					if( !empty( $d[$h_csv]  ) && !in_array($d[$h_csv], $filter_collumns) ) {
						$filter_collumns[] = $d[$h_csv];
					}

					
				}





				//crreate new files
				foreach($filter_collumns as $k => $filter_collumn) {
					// if( $k > 0 ) {

						$new_file[$k] = 'file_' . $k .'.csv';
						$temp_file = fopen($temp_directory . $new_file[$k], 'w');

						fputcsv($temp_file, $lines[0]);

						foreach($lines as $d) {


							if( !empty($d) && $d[$h_csv] == $filter_collumn ) {
								fprintf($temp_file, chr(0xEF).chr(0xBB).chr(0xBF));

								fputcsv($temp_file, $d);
							}

							
						}

						fclose( $temp_file);

						 // add this file to the ZIP folder
		  				// $zip->addFile( $zip_filepath,  __DIR__  . '\\' . $temp_directory . $new_file[$k] );

						echo $path = "temp_files/".$new_file[$k];
						if(file_exists($path)){
							$zip->addFromString(basename($path),  file_get_contents($path));  
						}
						else{
							echo"file does not exist";
						}




		  				// now delete this CSV file
						// if(is_file($temp_directory . $new_file[$k])) {
						// 	unlink($temp_directory . $new_file[$k]);
						// }

					// }

				}

			}

				var_dump($filter_collumns);
				die;

$zip->close();


header('Content-Type: application/zip');
header('Content-disposition: attachment; filename='.$zipname);
header('Content-Length: ' . filesize($zipname));
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