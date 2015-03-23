<?php

class Database
{
	private $handle;
	private $statement;

	/**
	 * Create connection
	 * @param string $hostname
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 */
	public function __construct($config)
	{
		$dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['database'];
		$options = [
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		];

		try {
			$this->handle = new PDO($dsn, $config['username'], $config['password'], $options);
		} catch (PDOException $e) {
			if ($config['debug']) {
				die($e->getMessage());
			} else {
				die('Database connection failed');
			}
		}
	}

	/**
	 * Prepare a statement
	 * @param string $sql
	 */
	public function prepare($sql, $values = false)
	{
		$this->statement = $this->handle->prepare($sql);
		$this->bind($values);
	}

	/**
	 * Bind parameters to statement
	 * @param array $values Values as parameter => value pairs
	 */
	private function bind($values)
	{
		if (is_array($values)) {
			foreach ($values as $param => $value) {
				$type = $this->getType($value);
				$this->statement->bindValue($param, $value, $type);
			}
		}
	}

	/**
	 * Get parameter type for binding it
	 * @param mixed $value
	 * @return integer
	 */
	private function getType($value)
	{
		if (is_int($value)) {
			$type = PDO::PARAM_INT;
		} else if (is_bool($value)) {
			$type = PDO::PARAM_BOOL;
		} else if (is_null($value)) {
			$type = PDO::PARAM_NULL;
		} else {
			$type = PDO::PARAM_STR;
		}

		return $type;
	}

	/**
	 * Execute a prepared statement
	 * @return boolean
	 */
	public function execute()
	{
		return $this->statement->execute();
	}

	/**
	 * Execute a prepared statement and return the first result
	 * @return array
	 */
	public function query()
	{
		$this->execute();
		return $this->statement->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * Execute a prepared statement and return all results
	 * @return array
	 */
	public function queryAll()
	{
		$this->execute();
		return $this->statement->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Get the ID of the last inserted row
	 * @return mixed A string on success, otherwise an IM001 SQLSTATE
	 */
	public function lastInsertId()
	{
		return $this->handle->lastInsertId();
	}

	/**
	 * Get the number of rows affected by the last execute
	 * @return integer
	 */
	public function rowCount()
	{
		return $this->statement->rowCount();
	}

	/**
	 * Begin a database transaction
	 * @return boolean
	 */
	public function beginTransaction()
	{
		return $this->handle->beginTransaction();
	}

	/**
	 * End the database transaction
	 * @return boolean
	 */
	public function endTransaction()
	{
		return $this->handle->commit();
	}

	/**
	 * Cancel the database transaction
	 * @return boolean
	 */
	public function cancelTransaction()
	{
		return $this->handle->rollBack();
	}
}
