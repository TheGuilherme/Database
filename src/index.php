<?php

// Required Database.php
require_once 'classes/Database.php';

$select = Database::get()->select("SELECT * FROM `messages`");
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>PHP Database helper</title>
</head>
	<body>
		<h1><?php echo $select[0]->name; ?></h1>
		<h3><?php echo $select[0]->email; ?></h3>

		<p>
			<strong>Sent for you: </strong> <?php echo $select[0]->message; ?>
		</p>
	</body>
</html>
