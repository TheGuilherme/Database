<?php

// Required settings for database.php
require_once dirname(__DIR__) . '/settings/database.php';

/**
* Database helper.
* Extending PDO to use custom methods.
* 
* @author Guilherme Alves | contact@theguilherme.com
* @version 1.0
*/
class Database extends \PDO
{
	
	/**
	 * @var array Array of saved databases for reusing
	 */
	protected static $_instances = array();

	/**
	 * Static method get.
	 * 
	 * @param array | bool $group 
	 * @return Database
	 */
	public static function get($group = false)
	{
		// Determining if exists or it's not empty, then use default group defined in database.php
		$group = !$group ? array(
			'type' => TYPE,
			'host' => HOST,
			'name' => NAME,
			'user' => USER,
			'pass' => PASS
		) : $group;

		// Group information
		$type = $group['type'];
		$host = $group['host'];
		$name = $group['name'];
		$user = $group['user'];
		$pass = $group['pass'];

		// ID for database based on the group information
		$id = $type . $host . $name . $user . $pass;

		// Checking if the same

		if (isset(self::$_instances[$id]))
		{
			return self::$_instances[$id];
		}

		try
		{
			/**
			 * I've run into problem where
			 * SET NAMES "UTF8" not working on some hostings.
			 * 
			 * Specifiying charset in DSN fixes the charset problem perfectly!
			 */
			$instance = new self($type . ':host=' . $host . ';dbname=' . $name . ';charset=utf8', $user, $pass);
			$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// Setting Database into $_instances to avoid duplication
			self::$_instances[$id] = $instance;

			return $instance;
		}
		catch (PDOException $e)
		{
			die($e->getMessage());
		}
	}

	/**
	 * Run raw sql queries.
	 * 
	 * @param string $query sql command 
	 * @return query
	 */
	public function raw($query)
	{
		return $this->query($query);
	}

	/**
	 * Method for selecting records from a dabase.
	 * 
	 * @param string $query sql query 
	 * @param array $params named params
	 * @param object $fetchMode
	 * @param string $class class name
	 * @return array returns an array of records
	 */
	public function select($query, $params = array(), $fetchMode = PDO::FETCH_OBJ, $class = '')
	{
		$statement = $this->prepare($query);

		foreach ($params as $key => $value)
		{
			if (is_int($value))
			{
				$statement->bindValue("$key", $value, PDO::PARAM_INT);
			}
			else
			{
				$statement->bindValue("$key", $value);
			}
		}

		$statement->execute();

		if ($fetchMode === PDO::FETCH_CLASS)
		{
			return $statement->fetchAll($fetchMode, $class);
		}
		else
		{
			return $statement->fetchAll($fetchMode);
		}
	}

	/**
	 * Insert method.
	 * 
	 * @param string $table table name
	 * @param array $params array of columns and values
	 */
	public function insert($table, $params)
	{
		ksort($params);

		$names = implode(',', array_keys($params));
		$values = ':' . implode(', :', array_keys($params));

		$query = "INSERT INTO `$table` ({$names}) VALUES ({$values})";

		$statement = $this->prepare($query);

		foreach ($params as $key => $value)
		{
			$statement->bindValue(":$key", $value);
		}

		$statement->execute();

		return $this->lastInsertId();
	}

	/**
	 * Update method.
	 * 
	 * @param string $table table name 
	 * @param array $params array of columns and values
	 * @param type $where array of columns and values
	 */
	public function update($table, $params, $where)
	{
		ksort($params);

		$field_details = null;

		foreach ($params as $key => $value)
		{
			$field_details .= "$key = :field_$key";
		}
		$field_details = rtrim($field_details, ',');

		$where_details = null;

		$i = 0;

		foreach ($where as $key => $value)
		{
			if ($i === 0)
			{
				$where_details .= "$key = :where_$key";
			}
			else
			{
				$where_details .= " AND $key = :where_$key";
			}
			$i++;
		}
		$where_details = ltrim($where_details, ' AND ');

		$query = "UPDATE `$table` SET $field_details WHERE $where_details";

		$statement = $this->prepare($query);

		foreach ($params as $key => $value)
		{
			$statement->bindValue(":field_$key", $value);
		}

		foreach ($where as $key => $value)
		{
			$statement->bindValue(":where_$key", $value);
		}

		$statement->execute();

		return $statement->rowCount();
	}

	/**
	 * Delete method.
	 * 
	 * @param string $table table name 
	 * @param array $where array of columns and values
	 * @param int $limit limit number of records
	 */
	public function delete($table, $where, $limit = 1)
	{
		ksort($where);

		$where_details = null;

		$i = 0;

		foreach ($where as $key => $value)
		{
			if ($i === 0)
			{
				$where_details .= "$key = :$key";
			}
			else
			{
				$where_details .= " AND $key = :$key";
			}
			$i++;
		}
		$where_details = ltrim($where_details, ' AND ');

		// If limit is a number use a limit on the query
		if (is_numeric($limit))
		{
			$use_limit = "LIMIT {$limit}";
		}

		$query = "DELETE FROM `$table` WHERE $where_details $use_limit";

		$statement = $this->prepare($query);

		foreach ($where as $key => $value)
		{
			$statement->bindValue(":$key", $value);
		}

		$statement->execute();

		return $statement->rowCount();
	}

	/**
	 * Truncate method
	 * 
	 * @param string $table table name
	 */
	public function truncate($table)
	{
		return $this->exec("TRUNCATE TABLE `$table`");
	}
}