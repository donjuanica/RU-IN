<?php 

$GLOBALS['IS_DEV'] = false;
//$GLOBALS['IS_DEV'] = true;

set_include_path(get_include_path()
	 . PATH_SEPARATOR . '../PEAR' 
	 . PATH_SEPARATOR . '../PEAR/PEAR' 
	 . PATH_SEPARATOR . '/home/epicadve/public_html/inorout/PEAR/PEAR' 
	 
	);



define('SITE_NAME', 'Adobe\'s In or Out');
define('FOOTER_MESSAGE','&copy; Copyright 2008-2012 Travis Whitney');

define('DATABASE_HOST','localhost');
define('DATABASE_USER','epicadve_adobe');
define('DATABASE_PASS','@dob3Ru1es');
define('DATABASE_NAME','epicadve_inorout_adobe');



function update_player_keys($player_id = null) {
	$db = new MySQL();
	$sql = "
	UPDATE IGNORE `players` AS p
	SET p.key = MD5(CONCAT(p.player_id,'dirty key is dirty lol 54321',p.email))
	WHERE 1
	AND p.key IS NULL 
	";
	if(isset($player_id) == true && $player_id > 0) $sql.= " AND p.player_id = '". $db->verifyVal($player_id) ."' " ;
	 else $sql.= " AND p.active = 1 ";
	$db->Execute($sql);
	return $db->Affected_Rows();
}

class MySQL {
        var $host ;
        var $database;
        var $user ;
        var $pass ;
        /**
        * @var string link_id    Points to the selected Database Connection.
        */
        var $link_id;
        /**
        * @var string    Result  Points to the last Query's Result (not an array)
        */
        var $Result;
        /**
        * @var  array Record Contains 1 row from the last Query's Result
        */
        var $Record = array();
        /**
        * @var  array data_array Contains ALL rows from the last Query's Result
        */
        var $data_array = array();
        /**
        * @var int  row Keeps track of the current row being pointed to by $this->Result
        */
        var $row;
        /**
        * @var string query  The last Query called
        */
        var $query;
               

        /**
        * Constructor function
        * Connects to our Database using our username and password (not very secure) and selects the appropriate Database
        *
        * @return Boolean True
        */
        function __construct() {			
						
			$this->host     = DATABASE_HOST;
			$this->user     = DATABASE_USER;
			$this->password = DATABASE_PASS;
			$this->database = DATABASE_NAME;
		
            $this->link_id = mysqli_connect($this->host,$this->user,$this->password) or $this->Error(mysqli_error($this->link_id));
            $DB = mysqli_select_db($this->link_id,$this->database) or $this->Error(mysqli_error($this->link_id));
            return true;
        }

        /**
        * Performs an SQL query $sql and saves the result identifier as $this->Result
        * If the SQL query isn't valid, the function Error() is called, which displays the error message and code
        * @param string $sql SQL query
        * @param string $location Location from which this query is being called.
        * @return string Result identifier
        */
        function Query($sql,  $file, $line, $location) {
            $this->Result = mysqli_query($this->link_id,$sql) or $this->Error(mysqli_error($this->link_id), $location, $file, $line, $sql);
            $this->query = $sql;
            # DEBUG   print $sql;
            return $this->Result;
        }

        /**
        * Performs an SQL query $sql and saves the result identifier as $this->Result
        * If the SQL query isn't valid, the function Error() is called, which displays the error message and code
        * @param string $sql SQL query
        * @return string Result identifier
        */
        function Execute($sql) {
			$result=0;
            $this->Result = mysqli_query($this->link_id,$sql);
            $this->query = $sql;
            # DEBUG   print $sql;

	        //Get error now so that it is not overwritten.
	        $mysqlerr = mysqli_error($this->link_id);
			
	        if ( !$this->Result ) {
				print "<br>error = $mysqlerr<br>";
	            //trigger_error($mysqlerr,$sql);
	            return false;
	        }
			
			//print $sql;
            return $this->Result;
        }
		
		

        /**
        * Fetches the current $row from the current $Result as an associative array $this->Record.
        * This is an associative array, it has no number indices. If you want number indices, use FetchRow() instead.
        * @return array Current Row
        */
        function FetchArray() {
                $this->Record = mysqli_fetch_array($this->Result, MYSQLI_ASSOC);
                //$this->Clean();
                return $this->Record;
        }

        /**
        * Fetches the current $row from the current $Result as a numerical array $this->Record.
        *  This is a numerical array, it has no associative indices. If you want associative indices, use FetchArray() instead.
        * @return array Current Row
        */
        function FetchRow() {
                $this->Record = mysqli_fetch_row($this->Result);
                return $this->Record;
        }

        /**
        * Fetches the current $row from the current $Result as an associative array $this->Record.
        * This is an associative array, it has no number indices. If you want number indices, use FetchRow() instead.
        * @return array Current Row
        */
        function Last_Insert_ID() {
                $this->Last_Insert_ID = mysqli_insert_id($this->link_id);
                //$this->Clean();
                return $this->Last_Insert_ID;
        }

        function Error_Message() {
                $this->Error = mysqli_error($this->link_id);
                //$this->Clean();
                return $this->Error;
        }
       
         /**
        *  "Cleans" the current row ($Record array) by stripping slashes from it
        *  You would use this when a string might have been saved with slashes to escape special characters
        * @return array Current Row
       */
        function Clean() {
                foreach(@$this->Record as $Key => $Val)
                {
                        $this->Record[$Key] = stripslashes($Val);
                }
                return $this->Record;
        }
		

        function TotalRows() {
                $this->Rows = mysqli_num_rows($this->Result);
                return $this->Rows;
        }

        function Affected_Rows() {
                $this->Affected_Rows = mysqli_affected_rows($this->link_id);
                return $this->Affected_Rows;
        }

        /**
        *  Closes the MySQL connection
        * The $record can be any array, but mostly a result of an SQL query. E.g: $this->Record or $this->data_array
        * @return boolean True if successful
        */
        function Close() {
                mysqli_close($this->link_id);
                return true;
        }

        /**
        * Displays the provided $Text as a MySQL Query error message
        * The text will be a generic mysqli_error() message with the location from which the query was called.
        * @param string $Text  MySQL Error Message
        * @param string $location The location from which the query was called
        */
        function Error($Text, $location = "not given", $file = "unknown file", $line = "unknown line", $sql) {
                echo '<div align="left">';
                echo "<em>MySQL Error!</em>
                    <ul>
                        <li><em>File</em>: $file</li>
                        <li><em>Line</em>: $line</li>
                        <li><em>Section</em>: $location</li>
                    </ul>";
                echo '<hr width="40%">';
                echo $Text;
                echo "<p>$sql</p>";
                echo '</div>';
                exit();
        }

        /**
        * Unsets all unnecessary variables related to the MySQL functions
        */
        function removeMysqlVars() {
            unset ($this->link_id);
            unset ($this->Result);
            unset ($this->Record);
            unset ($this->data_array);
            unset ($this->row);
            unset ($this->query);       
        }
       
        function getInfo($result) {
            return mysqli_info($result) ;
        }
		

    function escapeString($strValue) {
    	$strValue = (string)$strValue;  //ensure the value is a string
    	$retval = false;

    	//if there was an error returned, or no link id, escape in another way
    	if($retval === false)  {
    		//escape the value without the DB connection
    		$retval = mysql_escape_string($strValue);

//    		//send a SEV3 email
//    		sev_email(3, 'DATABASE PARAMETER VALIDATION', 'The mysql_real_escape_string() function called returned an error',
//    			"The call to mysql_real_escape_string returned an error when called in (Doba Web) Database.escapeString(). The value, '".$strValue.
//    			"' was escaped using mysql_escape_string instead, and was returned as '".$retval."'\n\n. Please make sure this is correct, otherwise there ".
//    			"could be a problem with the data. This usually occurs due to a lost database connection.");
    	}

    	return $retval;
    }

    /**
	 * This function accepts a single value or an array of parameters and performs validation on them to ensure that they do not
	 * contain any sql issues such as invalid characters or injection. Any parameters passed into the database should be sent through
	 * this function to prevent SQL injection.
	 *
	 * @param mixed $arrparams The parameters to validate.
	 * @return mixed An array of validated parameters.
	 */
	function validateParams($params)  {
		$retval = null;
		if(!empty($params))  {
			if(true == is_array($params)){
				$retval = array();
				foreach	($params as $par)  {
					$retval[] = $this->verifyVal($par);
				}
			}
			else  {
				$retval = $this->verifyVal($params);
			}
		}
		return $retval;
	}

	/**
	 * Private function which validates a single parameter and returns it.
	 *
	 * @param mixed $val
	 * @return mixed The validated parameter.
	 */
	function verifyVal($val)  {
        //if magic quotes is on, strip slashes
        if(get_magic_quotes_gpc())  {
        	$val = stripslashes($val);
        }
        //escape any mysql sql injection issues

        $val = $this->escapeString($val);

        return trim($val);
    }
}


function stripslashes_array($data) {
   if (is_array($data)){
       foreach ($data as $key => $value){
           $data[$key] = stripslashes_array($value);
       }
       return $data;
   }else{
       return stripslashes($data);
   }
}




?>