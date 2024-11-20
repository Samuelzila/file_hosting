<?php
include_once "api.php";

//admin safety
	//Verify if user is valid.
	if (!user_login($_COOKIE['username'], $_COOKIE['password'], true)) {
		header('Location: index.php');
		exit();
	}

	//Verify if user is an administrator.
	if (!verify_admin($_COOKIE['username'])) {
		header('Location: index.php');
		exit();
	}

	/* MANAGE BUTTONS */

	//Revoke user access
	if (isset($_POST['delete_user'])) {
		//$_POST['delete_user'] is set if the form is submited, and its value is a username.
		delete_user($_POST['delete_user']);
	}

	// Authorize user access as guest
	if (isset($_POST['allow_guest'])) {
		$users = get_unverified_users();
		$username = $_POST['username'];
		$password = $users[$username]['password'];

		create_user($username, $password, true, false, false);
		remove_unverified_user($username);
	}

	// Authorize user access
	if (isset($_POST['allow_user'])) {
		$users = get_unverified_users();
		$username = $_POST['username'];
		$password = $users[$username]['password'];

		create_user($username, $password, false, false, false);
		remove_unverified_user($username);
	}

	// Deny user access
	if (isset($_POST['deny_user'])) {
		remove_unverified_user($_POST['username']);
	}

	//Toggle guest access
	if (isset($_POST['toggle_guest'])) {
		edit_user($_POST['toggle_guest'], "", !user_is_guest($_POST['toggle_guest']), false);

	}
	
	//Toggle administrator status
	if (isset($_POST['toggle_admin'])) {
		edit_user($_POST['toggle_admin'], "", false, !verify_admin($_POST['toggle_admin']));

	}

?>
<!DOCTYPE html>
<html>
<head>
<?php
include "header.php";
?>
</head>
<body>
<a href="content.php" class="button" style="position:fixed;top:1em;left:1em;">Page principale</a>
<main>
<div class="box" style="margin-top:10vh;"><h3 align="center">User Management</h3>
<?php
	//List all users
	$users = get_users();
	foreach($users as $username => $user) {
		$admin_class = verify_admin($username) ? "toggle_on" : "toggle_off";
		$guest_class = user_is_guest($username) ? "toggle_off" : "toggle_on";
		
		echo '<form style="text-align:center;" method="post">
			<label>'.$username.'</label>	
			<label for="toggle_admin:'.$username.'" class="clickable_text '.$admin_class.'"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hammer" viewBox="0 0 16 16">
				<path d="M9.972 2.508a.5.5 0 0 0-.16-.556l-.178-.129a5.009 5.009 0 0 0-2.076-.783C6.215.862 4.504 1.229 2.84 3.133H1.786a.5.5 0 0 0-.354.147L.146 4.567a.5.5 0 0 0 0 .706l2.571 2.579a.5.5 0 0 0 .708 0l1.286-1.29a.5.5 0 0 0 .146-.353V5.57l8.387 8.873A.5.5 0 0 0 14 14.5l1.5-1.5a.5.5 0 0 0 .017-.689l-9.129-8.63c.747-.456 1.772-.839 3.112-.839a.5.5 0 0 0 .472-.334z"/>
			  </svg></label>
			<input type="submit" value="'.$username.'" name="toggle_admin" id="toggle_admin:'.$username.'">
			<label for="toggle_guest:'.$username.'" class="clickable_text '.$guest_class.'"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
				<path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
			  </svg></label>
			<input type="submit" value="'.$username.'" name="toggle_guest" id="toggle_guest:'.$username.'">
			<label for="usrdel:'.$username.'" class="clickable_text"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
				<path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z"/>
			  </svg></label>
			<input type="submit" value="'.$username.'" name="delete_user" id="usrdel:'.$username.'">
			<br></form>';
	}
?>
</div>
<div class="box"><h3 align="center">User Validation</h3>
<?php
	//List unverified users and create relevent buttons.
	$users = get_unverified_users();
	
	foreach($users as $username => $user) {
		echo '
		<form style="text-align:center;" method="post">
			<label>'.$username.'</label>
			<label for="usraccg:'.$username.'" class="clickable_text" style="margin:0.05em;">&#x2714</label>
			<label for="usracc:'.$username.'" class="clickable_text" style="margin:0.05em;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
				<path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
			  </svg></label>
			<label for="usrden:'.$username.'" class="clickable_text" style="margin:0.05em;">&#x2718</label>
				<input type="submit" name="allow_guest" id="usraccg:'.$username.'">	
				<input type="submit" name="allow_user" id="usracc:'.$username.'">
				<input type="submit" name="deny_user" id="usrden:'.$username.'">
				<input type="hidden" name="username" value="'.$username.'">
			</form><br>';
	}
?>
</div>
</main>
</body>
</html>