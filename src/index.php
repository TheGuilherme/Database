<?php

// Required Database.php
require_once 'classes/Database.php';

/**
 * How to use?
 * 
 * Configure the file in 'settings/database.php'
 * 
 * Instance de class Database::get()
 * 
 * Once this is done, select the required method, here we will use the select method.
 * 
 * Database::get()->select("SELECT * FROM `table_name`");
 * 
 * OR
 * 
 * Database::get()->select("SELECT * FROM `table_name` WHERE id = :id", array(
 * 	':id' => 1
 * ));
 */

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