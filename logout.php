<?php
//logout
	setcookie('username',$_COOKIE['username'],time());
	setcookie('password',$_COOKIE['password'],time());
	header('Location: index.php');
?>
<!DOCTYPE html>
<html>
<head>
<?php
include "header.php";
?>
</head>
<body>
loging out...
</body>
</html>