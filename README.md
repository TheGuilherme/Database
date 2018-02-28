# Database
Database connect with PDO

# How to use?
Configure the file in **'settings/database.php'**

Instance de class:

`Database::get()`

Once this is done, select the required method, here we will use the select method.

`Database::get()->select("SELECT * FROM table_name");`

# OR
`Database::get()->select("SELECT * FROM `table_name` WHERE id = :id", array(
  ':id' => 1
));`
