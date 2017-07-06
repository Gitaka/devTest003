<?php

class DbConnect{
	private $conn;

	function __construct(){

	}

	//establish db connection and return a db handler

	function connect(){

		include_once dirname(__FILE__).'/config.php';

		//connectin to mysqsl db
		$this->conn = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);

		//check for db connection errors
		if(mysqli_connect_errno()){
			echo'Failed to connect to mysql'.mysqli_connect_errno();
            exit;
 		}
 		//returning connection resource
 		return $this->conn;
	}
}

?>