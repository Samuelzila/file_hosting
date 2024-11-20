<?php
include_once "./api.php";
?>
<!DOCTYPE html>
<html>
<head>
<?php
include "header.php";
?>
</head>
<body>
<main>
	<h1>Plateforme d'hébergement de fichiers</h1>
	<div class="box" style="margin-top:11vh;">
	<form action="index.php" method="post" class="flex-column">
		<label>Nom d'utillisateur</label><br>
		<input type="text" name="username" autocomplete="off"><br>
		<label>Mot de passe</label><br>
		<input type="password" name="password"><br>
		<label for="submit" class="button" style="align-self:flex-end;margin-top:1em;">Connexion</label>
		<input type="submit" name="submit" id="submit">
	</form>
<?php
	//User login
	if (isset($_POST['submit'])) {
		//Checks if all fields are filled
		if(empty($_POST['username'] || empty($_POST['password']))){
			echo '<p class="errorMessage">Veuillez remplir tous les champs</p>';
		}
		else
		{
			//Attempts to login user.
			if (!user_login($_POST['username'], $_POST['password'])) {
				echo '<p class="errorMessage">Le nom d\'utilisateur, le mot de passe ou les deux sont incorrects</p>';
			}
			else {
				header('Location: content.php');
			}
		}
	}

	//Extends cookie duration if they already exist.
	elseif((isset($_COOKIE['username']) && isset($_COOKIE['password'])) && (!empty($_COOKIE['username']&&!empty($_COOKIE['password'])))) {
			user_cookies_extend();
			header('Location: content.php');
		}
?>
</div>
<div align="center" class="box"><a class="clickable_text" href="inscription.php">Inscription</a></div>
</main>
</body>
</html>