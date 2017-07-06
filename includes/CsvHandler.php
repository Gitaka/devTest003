<?php

  class CsvHandler{

  	private $connection;
  	private $csvFile;
  	private $activeMQ_jsonObject;

  	

  	function __construct(){
  		require_once dirname(__FILe__).'/DbConnect.php';


  		
        $db = new DbConnect();
        $this->connection = $db->connect();

    
  	}
    
    public function readCSVFile(){

    	$file_handle = fopen($this->csvFile,'r');
        
        $csvData = array();

    	while(!feof($file_handle)){
    		$csvData[] = fgetcsv($file_handle,1024);
    	}
    	fclose($file_handle);

    	return @ $this->parserCSVData($csvData);
    
    }

     private function parserCSVData($data){
       $keys = array_keys($data);
       $AT_array = array(); //array to hold rows with A or T values at index 4

       for ($i=0; $i < count($data); $i++) { 
       	 foreach(($data[$keys[$i]]) as $key=>$value){
            //$value is returned as a string,explode to array
            $valuesArray = explode('|',$value);

            $CG_array = array();  //aray to hold rows with C or G values at index 4
              

            if($valuesArray[4] === 'C' || $valuesArray[4] === 'G' ){
            	$CG_array[] = $value;
                    //insert to db the rows with C or G at index 4
            	// $this->insertToDb($CG_array);
            	
            }else if($valuesArray[4] === 'A' || $valuesArray[4] === 'T' ) {
            	//put everything else to an activeMQ queue;
            	$AT_array[] = $value;
                   
                 $this->activeMQ_jsonObject = json_encode($AT_array);
          
               
            }
       	 }
       }

        //simulate publishing the AT_array into activeMq
       $this->publishToQue(json_encode($AT_array));
       
    }
    
    private function insertToDb($cg_array){
    	$data = $cg_array;

    	$queryString = $this->connection->prepare("INSERT INTO cg_table(value) VALUES(?)");


        foreach($data as $c_g){
            //echo $c_g."</br>";
             $queryString->bind_param("s",$c_g);
             $queryString->execute();
         }

         $queryString->close();
         //echo"data inserted into database";
    }

    public function readDBRows_toCSV(){
    	$queryString = $this->connection->query("SELECT * FROM cg_table");
         
         if($queryString->num_rows > 0){
               
         	   $data = array();
               while($row = $queryString->fetch_assoc()){
               	   $data[] = $row["value"];
               	 
               }

               $this->genDBCSV($data);
               
    		}else{
    			return Null;
    		}

    	$queryString->close();
    }
    
  

    public function genDBCSV($array_data){
    	//the file to write the csv data to
    
     	 $file = fopen('dbRows.csv','w');

		 $arrayColumns = array('0','1','2','3','4','5','6','7','8','9','10','11');
		 //save the column headers into the csv file
		 fputcsv($file,$arrayColumns,'|');
		
		 foreach ($array_data as $row) {
 	         fputcsv($file,explode('|',$row),'|');
          }
        //close the file
         fclose($file);
         fwrite(STDOUT,"csv file dbRows.csv has been created,contains rows where C or G are values at coloumn 5"."\n");
    }

    private function publishToQue($array){

    if(class_exists('Stomp')){
		
		$queue  = '/queue/activeMqDevTest003';
        $msg    = json_encode($array);
		
		$stomp = new Stomp('tcp://localhost:61613');
		$stomp->connect();
		$stomp->send($queue, $msg);

		// subscribe to messages from the queue 
		$stomp->subscribe($queue);

		$frame = $stomp->readFrame();

		if ($frame->body === $msg) {
		     $this->sendDataTocsv($msg);
		    // acknowledge that the frame was received 
		    $stomp->ack($frame);
		}

		// close connection 
		unset($stomp);
       }else{
       	//if connection to activeMq is not available,go ahead and write the data to csv file
		   $data = json_decode($array);

	       $file = fopen('activeMqRows.csv','w');

		   $arrayColumns = array('0','1','2','3','4','5','6','7','8','9','10','11');
			 //save the column headers into the csv file
		   fputcsv($file,$arrayColumns,'|');
			
	       
	 
	       for ($i=0; $i < count($data); $i++) { 
	             
	            fputcsv($file,explode('|',$data[$i]),'|');
	       	}
		    
	        //close the file
	         fclose($file);
	         

	       fwrite(STDOUT,"simulated activemq jsondata written to csv file activeMqRows.csv.It contains rows where A or T are values at column 5"."\n");
        }


    }
 
    private function sendDataTocsv($array){
      $data = json_decode($array);

       $file = fopen('activeMqRows.csv','w');

	   $arrayColumns = array('0','1','2','3','4','5','6','7','8','9','10','11');
		 //save the column headers into the csv file
	   fputcsv($file,$arrayColumns,'|');
		
       
 
       for ($i=0; $i < count($data); $i++) { 
             
            fputcsv($file,explode('|',$data[$i]),'|');
       	}
	    
        //close the file
         fclose($file);
    }
  	public function createCSVFile($file){

        $this->csvFile = $file;
        //open the csv file  for write operations
		 
		 $file = fopen($file,'w');

		 $arrayColumns = array('0','1','2','3','4','5','6','7','8','9','10','11');
		 //save the column headers into the csv file
		 fputcsv($file,$arrayColumns,'|');
		 //column 5 with 20% C or G as th values is at index 4
		$dataRows = array(
		             array('A','A','T','G','A','G','T','A','C','G','A','G'),
		             array('G','G','C','A','A','T','C','T','A','T','C','A'),
		             array('T','C','T','C','T','G','T','G','T','G','T','C'),
		             array('C','G','G','T','C','C','C','C','G','A','A','A'),//C
		             array('G','A','T','G','G','T','A','A','T','G','A','T'),//G
		             array('T','T','A','A','T','A','C','T','C','C','G','C'),
		             array('T','T','C','T','T','C','A','G','A','T','G','C'),
		             array('A','G','T','G','G','G','T','T','G','C','A','G'),//G
		             array('G','T','G','T','A','A','G','G','T','G','T','T'),
		             array('C','A','C','A','T','T','A','C','T','A','G','C'),

		             array('A','T','G','A','C','T','T','G','A','T','T','A'),//C
		             array('T','G','C','A','A','C','C','G','A','G','T','A'),
		             array('A','A','T','G','G','T','G','C','T','T','T','A'),//G
		             array('G','C','T','T','T','G','T','C','A','G','T','A'),
		             array('C','T','A','G','A','A','A','T','A','T','T','A'),
		             array('T','G','G','T','A','G','A','T','T','A','G','C'),
		             array('G','C','G','A','A','C','G','G','C','C','G','C'),
		             array('C','T','C','T','T','G','A','G','A','A','G','C'),
		             array('A','A','C','C','G','C','A','G','A','C','G','C'),//G
		             array('A','A','C','G','A','A','T','T','T','A','G','C'),

		             array('G','A','T','C','A','A','C','A','C','G','T','C'),
		             array('C','T','G','C','A','G','T','A','C','T','T','C'),
		             array('G','A','C','G','T','T','T','G','T','A','T','C'),
		             array('C','T','C','T','A','C','T','T','A','G','T','A'),
		             array('G','A','A','G','A','C','T','T','T','G','G','A'),
		             array('C','C','G','T','A','T','T','T','T','A','G','T'),
		             array('T','A','T','A','T','C','C','G','A','G','T','T'),
		             array('C','G','G','A','C','C','6','C','G','A','G','G'),//C
		             array('T','A','A','A','T','G','T','T','A','C','C','T'),
		             array('A','G','C','A','A','G','T','T','G','A','C','G'),

		             array('T','C','G','A','T','A','G','A','A','G','C','T'),
		             array('C','T','C','G','A','T','T','T','A','C','T','C'),
		             array('C','A','T','C','T','G','A','A','C','T','A','G'),
		             array('A','C','A','T','A','G','A','C','T','T','A','C'),
		             array('C','A','C','T','T','T','G','T','C','G','G','A'),
		             array('A','C','T','A','A','C','T','G','A','A','A','C'),
		             array('C','A','A','C','T','T','C','T','G','C','C','A'),
		             array('C','A','C','T','A','C','T','A','T','G','A','T'),
		             array('A','C','T','C','T','A','C','C','G','T','G','A'),
		             array('C','T','C','G','G','A','T','G','G','A','T','G'),//G

		             array('C','T','C','A','A','G','T','A','C','T','C','A'),
		             array('T','A','G','A','T','A','A','C','C','T','G','A'),
		             array('C','T','T','G','T','A','C','G','G','T','G','A'),
		             array('T','A','T','A','T','C','G','T','T','C','C','A'),
		             array('G','T','A','A','A','A','C','T','G','T','C','A'),
		             array('A','A','A','G','A','T','T','C','G','T','A','G'),
		             array('T','C','C','T','T','G','G','A','G','A','A','T'),
		             array('C','G','G','G','A','A','T','A','A','G','G','C'),
		             array('G','T','T','C','T','G','G','T','A','A','T','T'),
		             array('A','G','G','T','A','C','C','A','G','A','C','A'),

		             array('A','A','G','T','C','C','C','C','G','T','C','T'),//G
		             array('T','C','T','A','A','C','G','G','G','A','G','C'),
		             array('T','C','C','T','T','G','G','G','A','T','G','G'),
		             array('T','T','T','A','C','G','G','A','T','C','C','G'),//C
		             array('T','A','G','T','A','C','A','T','C','C','T','G'),
		             array('T','A','A','G','G','T','C','A','T','C','A','C'),//G
		             array('T','A','T','G','A','T','G','G','A','T','A','G'),
		             array('C','T','C','T','A','T','A','7','T','A','C','A'),
		             array('C','G','G','C','T','T','A','T','A','C','A','A'),
		             array('C','G','A','A','A','G','T','A','G','C','C','C'),

		             array('T','G','C','A','T','C','A','T','T','C','T','A'),
		             array('T','A','G','T','A','C','G','C','G','T','A','G'),
		             array('A','G','T','G','C','C','A','C','A','G','T','C'),//C
		             array('G','G','A','C','G','T','T','C','G','A','G','G'),//G
		             array('C','A','A','T','G','C','T','A','C','A','A','T'),//G
		             array('T','C','G','G','T','G','G','G','A','T','G','A'),
		             array('T','T','A','T','C','C','G','T','G','C','T','A'),//C
		             array('C','G','A','A','A','C','G','G','T','C','C','T'),
		             array('G','T','C','A','A','T','C','T','C','G','A','G'),
		             array('A','T','A','G','A','G','T','G','A','A','C','C'),

		             array('T','C','T','G','T','T','C','C','G','C','A','T'),
		             array('C','A','T','C','C','A','T','G','C','G','G','G'),//C
		             array('G','C','A','C','A','T','A','T','T','A','A','T'),
		             array('C','T','A','G','T','A','T','T','T','G','C','T'),
		             array('A','C','A','G','T','T','T','G','A','T','T','A'),
		             array('A','G','G','C','T','A','G','G','A','C','G','A'),
		             array('G','A','G','C','T','T','G','C','A','C','C','T'),
		             array('T','A','T','G','A','A','C','C','T','T','C','T'),
		             array('G','C','C','G','A','T','C','A','C','G','T','G'),
		             array('C','T','A','C','A','A','C','A','G','A','G','G'),

		             array('C','C','C','T','A','G','G','A','T','C','A','A'),
		             array('A','G','C','G','A','G','T','T','G','C','C','T'),
		             array('C','T','T','G','T','T','A','A','G','A','T','G'),
		             array('T','A','G','G','C','A','T','T','A','C','G','C'),//C
		             array('A','T','C','T','A','A','G','A','G','T','C','T'),
		             array('A','A','A','T','A','T','C','C','A','T','T','A'),
		             array('G','A','T','A','A','T','G','C','C','C','T','G'),
		             array('T','T','C','T','C','G','T','C','G','T','C','C'),//C
		             array('G','G','G','C','T','A','G','T','C','G','G','T'),
		             array('A','C','T','G','T','G','A','T','G','G','A','G'),

		             array('T','A','C','G','T','A','T','G','C','T','G','A'),
		             array('C','G','A','T','A','C','A','T','G','A','T','T'),
		             array('G','C','T','T','A','T','T','A','G','T','T','A'),
		             array('C','C','T','T','G','G','G','G','A','C','C','C'),//G
		             array('A','T','G','C','T','A','T','C','G','G','C','A'),
		             array('C','G','T','T','T','A','G','A','G','T','C','A'),
		             array('A','C','A','T','G','T','A','T','C','G','G','T'),//G
		             array('T','C','C','C','A','T','G','G','T','T','C','G'),
		             array('A','G','A','A','T','G','C','C','C','G','T','A'),
		             array('A','T','G','T','T','C','G','T','A','G','C','T')

		 	);
		 //save each row of data into the csv column

		 foreach ($dataRows as $row) {
		 	fputcsv($file,$row,'|');
		 }
 

		 //close the file
	     fclose($file);
	 
	     return  $this->csvFile;
  	} 
  }
?>