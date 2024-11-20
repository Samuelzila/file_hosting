<?php
include_once "api.php";

//file info
$id = $_POST['id'];
$idFormated = sprintf('%04d', $id);
$fileName = $_COOKIE['fname'];
$targetDir = "uploads/";
$targetFile = $targetDir.$fileName;
$fileNameRaw = pathinfo($fileName, PATHINFO_FILENAME); //file name with no extension
$fileType = pathinfo($fileName, PATHINFO_EXTENSION);
$tempDir = $targetDir.'tmp/'.$fileNameRaw.'/';
$tempFilePath = $tempDir.$fileNameRaw.'_'.$idFormated.'.tmp';

if (strtolower($fileType) == "php") {
	echo 'Error2';
	exit();
}

if (file_exists($targetFile)) {
	echo 'Error1';
	exit();
}

if (!is_dir($tempDir)) {
	mkdir($tempDir);
};

if (move_uploaded_file($_FILES['upload']['tmp_name'], $tempFilePath)) {
	//count/list files
	$files = glob($tempDir.'*');
	//make sure the Upload Success info is sent at the end only
	if (count($files) == $_POST['nbChunks'] && !file_exists($targetFile)) {
		echo count($files)+1;
	}
	//send progress
	else {echo count($files);}

	//detect if finished
	if (count($files) == $_POST['nbChunks'] && !file_exists($targetFile)) {
	//Process all chunks
		$resultFile = fopen($targetFile, 'a');
		//writes final file
		unset($files);
		$files = glob($tempDir.'*'); //make sure the glob is reliable
		foreach ($files as $value) {
			$currFile = file_get_contents($value);
			fwrite($resultFile, $currFile);
			unset($currFile);
			unlink($value);
		}

		fclose($resultFile);
		rmdir($tempDir);
		chmod($targetFile, 0777);

		register_file($fileName, $targetFile, user_is_guest($_COOKIE['username']));
	}
}
?>