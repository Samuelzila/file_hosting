<?php
include_once "api.php";
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
	<form action="inscription.php" method="post" class="flex-column">
		<label>Nom d'utillisateur</label><br>
		<input type="text" name="username" autocomplete="off"><br>
		<label>Mot de passe</label><br>
		<input type="password" name="password"><br>
		<label>Confimation du mot de passe</label><br>
		<input type="password" name="confirmPassword"><br>
		<label for="submit" class="button" style="align-self:flex-end;margin-top:1em">Envoyer</label>
		<input type="submit" name="submit" id="submit">
	</form>
	<?php
		//Acount creation
		if (isset($_POST['submit'])) { 
			//Makes sure all fields are filled.
			if(empty($_POST['username']) || empty($_POST['password']) || empty($_POST['confirmPassword'])){
				echo '<p class="errorMessage">Veuillez remplir tous les champs.</p>';
			}
			//Verifies that passwords match
			else if($_POST['password'] != $_POST['confirmPassword']) {
				echo '<p class="errorMessage">Veuillez vous assurer que le mot de passe et sa confirmation soient identiques</p>';
			}
			else{
				//Creates user and checks if there was an error.
				if (!propose_user($_POST['username'],$_POST['password'])) {
					echo '<p class="errorMessage">l\'utillisateur existe déjà</p>';
				}
				else {
					echo '<p class="successMessage">Votre requète a été envoyée, veuillez attendre l\'autorisation de nos administrateurs.</p>';
				}
			}
		}
	?>
	</div><br><br>
	<div class="box" style="text-align:center;"><a class="clickable_text" href="index.php">Connexion</a></div>
</main>
</body>
</html>