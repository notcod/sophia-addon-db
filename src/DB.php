<?php
namespace Sophia\Addon;

class DB
{
	private $DB;
	public function __construct($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME)
	{
		$this->DB = new \mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
		if ($this->DB->connect_error) die("Connection failed: " . $this->DB->connect_error);
		return $this->DB;
	}
	public function query($q)
	{
		$result = $this->DB->query($q);
		if ($this->DB->error)
			throw new \Exception($this->DB->error . " => [" . $q . "]");
		if (strpos(strtoupper($q), 'INSERT INTO') !== false)
			$result = $this->DB->insert_id;
		elseif (strpos(strtoupper($q), 'UPDATE') !== false)
			$result = $this->DB->affected_rows;

		return $result;
	}
	public function get($q, $time = 10)
	{

		$file = $_SERVER["DOCUMENT_ROOT"] . "/app/logs/query/" . $q . ".txt";
		if (file_exists($file) && filemtime($file) > (time() - $time)) {
			$myfile = fopen($file, "r") or die("Unable to open file!");
			$get = fread($myfile, filesize($file));
			fclose($myfile);
			return json_decode($get, true);
		} else {
			$result = $this->query($q)->fetch_all(MYSQLI_ASSOC);
			$myfile = fopen($file, "w") or die("Unable to open file!");
			fwrite($myfile, json_encode($result));
			fclose($myfile);
			return $result;
		}
	}
	public function fetch($q)
	{
		return $this->query($q . " LIMIT 1")->fetch_array(MYSQLI_ASSOC);
	}
	public function numRows($q)
	{
		return $this->check("SELECT count(id) FROM " . $q);
	}
	public function escape($q)
	{
		return $this->DB->real_escape_string($q);
	}
	public function check($q)
	{
		$data = $this->fetch($q);
		$data = is_array($data) ? end($data) : 0;
		return empty($data) ? 0 : $data;
	}
}
