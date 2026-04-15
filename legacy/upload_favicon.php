<?php


$fileDir = "images/" . \LeadMax\TrackYourStats\System\Company::getCustomSub() . "/"; //Sets upload directory
if (!file_exists($fileDir))
{
	mkdir($fileDir);
}
//
//$target_file = $fileDir . basename($_FILES["file1"]["name"]);
//
//$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);


$fileName = "favicon.ico";
//$fileName = $_FILES["file1"]["name"];
$fileTmpLoc         = $_FILES["file2"]["tmp_name"]; // File in the PHP tmp folder
$fileType           = $_FILES["file2"]["type"]; // The type of file it is
$fileSize           = $_FILES["file2"]["size"]; // File size in bytes
$fileErrorMsg       = $_FILES["file2"]["error"]; // 0 for false... and 1 for true
$fileDirAndFileName = $fileDir . $fileName;
$uploadOk           = 1; // Error handling
$uploadComplete     = false; // Error handling


// If file not chosen
if (!$fileTmpLoc)
{
	echo "ERROR: Please browse for a file before clicking the upload button.";
	$uploadOk = 0;
	die;
}


// Check if file already exists
if (file_exists($fileDirAndFileName))
{
	unlink($fileDirAndFileName);
}
// Check file size
if ($fileSize > 16777216)
{
	echo "Sorry, your file is too large.";
	
	$uploadOk = 0;
	die;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0)
{
	echo "Sorry, your file was not uploaded.";

// if everything is ok, try to upload file
}
else
{
	
	if (move_uploaded_file($fileTmpLoc, $fileDirAndFileName))
	{
		echo "$fileName upload is complete";
		$uploadComplete = true;
		
	}
	else
	{
		echo "move_uploaded_file function failed";
		die;
	}
}

// Get line count of file
if ($uploadComplete = true)
{
	$lineCount = 0;
	$handle    = fopen($fileDirAndFileName, "r");
	
	while (!feof($handle))
	{
		$line = fgets($handle);
		$lineCount++;
	}
	
	fclose($handle);
	
	// echo "<br><br>Imported " . $lineCount . " addresses to database.";
}


?>
