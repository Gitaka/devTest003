<?php

 require_once dirname(__FILe__).'/includes/DbConnect.php';
 
 $db = new DbConnect();
 $connection = $db->connect();




 function createDB_tbl($connection){
   $query  = "CREATE TABLE cg_table (id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,value VARCHAR(30) NOT NULL)";

   $queryString = $connection->prepare($query);
  
	if ($queryString->execute() === TRUE) {
	 
	     fwrite(STDOUT,"Table cg_table has been created successfully");
	} else {
	    
	    fwrite(STDOUT,"Error creating table cg_table");
	}

   $queryString->close();

 }

 createDB_tbl($connection);

?>