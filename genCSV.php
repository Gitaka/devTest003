<?php
include'./includes/CsvHandler.php';


fwrite(STDOUT,"Enter CSV File Name"."\n");
$file_name = rtrim(fgets(STDIN));

if(empty($file_name)){
	fwrite(STDOUT,"Please enter a csv file name");
}else{
	//initialize the CsvHandler class to generate csv file
	$handler = new CsvHandler();
	
	$csvFile = $handler->createCSVFile($file_name);
	
	fwrite(STDOUT,"csv file"." ".$file_name." "."has been created"."\n");

	$handler->readCSVFile();//read csv file created above and parse it
	$handler->readDBRows_toCSV(); //writeto csv files
}
?>