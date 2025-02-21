<?php
include_once "api.php";

//safety (authorized user validation)
//Attempts to login user.
if (!user_login($_COOKIE['username'], $_COOKIE['password'], true)) {
	clear_user_cookies();
	header('Location: index.php');
	exit();
}

//This will delete a file if the button to do so has been clicked.
if (isset($_POST['delete_file']) && verify_admin($_COOKIE['username'])) {
	delete_file($_POST['delete_file']);
}

//Toggle guest access
if (isset($_POST['toggle_guest_access']) && verify_admin($_COOKIE['username'])) {
	edit_file($_POST['toggle_guest_access'], "", !file_accessible_to_guests($_POST['toggle_guest_access']));
}

?>
<!DOCTYPE html>
<html>

<head>
	<?php
	include "header.php"
	?>
</head>

<body>
	<div>
		<a href="logout.php" class="button" style="position:fixed;top:1em;right:1em;">Déconnexion</a>
		<?php
		//verify if admin
		if (verify_admin($_COOKIE['username'])) {
			echo '<a href="administration.php" class="button" style="position:fixed;top:1em;left:1em;">Panneau d\'administration</a>';
		}
		?>
	</div>
	<main>

		<div class="box" style="margin-top:10vh;">
			<h3>Files:</h3>
			<?php
			//Display all registered files and create a link to them.
			$files = get_files();

			foreach ($files as $file) {
				//Only display the file if it is accessible to guests or the user is not a guest.
				if ($file['guest_access'] || !user_is_guest($_COOKIE['username'])) {
					echo '<form method="post" class="file_entry">';
					echo $file['filename'];
					echo ' (' . human_filesize($file["path"]) . ')';

					echo '<div class="file_buttons">';
					echo '<a class="clickable_text" download href="' . $file['path'] . '"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
		<path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
		<path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
	  </svg></a>';

					if (verify_admin($_COOKIE['username'])) {
						$guest_class = $file['guest_access'] ? "toggle_on" : "toggle_off";

						echo '<label for="delete_file:' . $file['filename'] . '" class="clickable_text"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
				<path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z"/>
			  </svg></label>';
						echo '<input type="submit" value="' . $file['path'] . '" name="delete_file" id="delete_file:' . $file['filename'] . '">';
						echo '<label for="toggle_guest_access:' . $file['filename'] . '" class="clickable_text ' . $guest_class . '"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
				<path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
			  </svg></label>';
						echo '<input type="submit" value="' . $file['path'] . '" name="toggle_guest_access" id="toggle_guest_access:' . $file['filename'] . '">';
					}

					echo '</div>';
					echo '</form>';
				}
			}
			?>
		</div>
		<?php
		//Display upload box
		echo <<< END
	<div class="box">
	<p>Veuillez séléctionner un fichier a importer.</p>
	<label for="choose_file" class="clickable_text">Parcourir...</label>
	<span class="clickable_text" onclick="uploadFile('choose_file', 0);">Téléverser</span>
	<input type="file" name="file" id="choose_file">
	<p id="uploadStatus" style="text-align:right;"></p>
	</div>
	END;


		?>
	</main>
	<script type="text/javascript">
		function uploadFile(fileInputId, fileIndex) {
			//send file name
			try {
				var fileName = document.getElementById('choose_file').files[0].name;
			} catch {
				document.getElementById('uploadStatus').innerHTML = `Mettre un fichier serait une bonne idée.`;
				document.getElementById('uploadStatus').classList.add('errorMessage');
				return false;
			}
			document.cookie = 'fname=' + fileName;

			//take file from input
			const file = document.getElementById(fileInputId).files[fileIndex];

			//create a chunk of data
			function readChunk(i, begining, ending) {
				return new Promise((res, err) => {
					let fr = new FileReader();
					let chunk = file.slice(begining, ending);
					fr.readAsArrayBuffer(chunk)
					fr.onload = () => {
						let ui8a = new Uint8Array(fr.result, 0);
						for (var e = 0; e < fr.result.length; e++) ui8a[e] = (fr.result.charCodeAt(e) & 0xff);
						let blob = new Blob([ui8a]);

						res(blob);
					}
					fr.onerror = () => {
						err(true);
					}
				});
			}

			//manage concurent requests to, well, not kill the computer
			function promiseMap() {
				let start = 0,
					chunkSize = 104857600, //size in bytes
					end = start + chunkSize,
					instance = 1,
					eofCounter = Math.ceil(file.size / chunkSize),
					concMax = 1, //php cannot handle more than one with perfection
					concCount = 0,
					done = 0,
					serverDone = 0,
					percentage = 0;

				console.log('Amount of packets to send: ' + eofCounter);

				function run() {
					while (start < file.size && concCount < concMax) {
						++concCount;
						readChunk(instance, start, end).then((res) => {
							//send to server
							let ajax = new XMLHttpRequest();
							ajax.open("POST", 'uploader.php', true);
							//ajax.setRequestHeader('Content-type', 'multipart/form-data');

							ajax.onreadystatechange = function() {
								if (ajax.readyState == 4 && ajax.status == 200) {
									serverDone++;
									//Manage Answer
									if (!isNaN(Number(this.responseText))) {
										let response = Number(this.responseText);
										//track progress in console
										let servPercentage = Math.round((this.responseText / eofCounter) * 100);
										console.log(`Server received ${response} out of ${eofCounter} / ${servPercentage}%`);
										//track progress for user
										if (percentage === 100) {
											if (response === eofCounter + 1) {
												document.getElementById('uploadStatus').innerHTML = 'Fichier envoyé avec succès !';
												document.getElementById('uploadStatus').classList.add('successMessage');
												console.log('Success, you may exit');
											} else {
												if (response + 1 === eofCounter) {
													document.getElementById('uploadStatus').innerHTML = 'Traitage...';
												} else {
													document.getElementById('uploadStatus').innerHTML = `Téléchargement sur le serveur: ${servPercentage}%`;
												}
											}
										}
									}
									//Manage Exception
									if (this.responseText !== '' && isNaN(Number(this.responseText))) {
										if (this.responseText == 'Error1') {
											document.getElementById('uploadStatus').innerHTML = 'Un fichier du même nom existe déjà.';
											document.getElementById('uploadStatus').classList.add('errorMessage');
										}
										console.log(this.responseText);
									}
								}
							}
							let fd = new FormData();
							fd.append('upload', res);
							fd.append('nbChunks', eofCounter);
							fd.append('id', instance++);
							ajax.send(fd);
							--concCount;

							//track progress
							done++;
							percentage = Math.round((done / eofCounter) * 100);
							console.log(`Sent: ${fd.get('id')} out of ${eofCounter} / ${percentage}%`);
							document.getElementById('uploadStatus').innerHTML = `Téléversement vers le serveur: ${percentage}%`;
							if (percentage === 100) {
								console.log('Client done');
							}

							run();
						});
						start = end;
						end = start + chunkSize;
					}
				}
				run();
			}
			promiseMap();
		}
	</script>
</body>

</html>
