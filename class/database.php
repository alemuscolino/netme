<?php
class Database
{
	# @var, MySQL Hostname
	protected static $hostname = 'localhost';

	# @var, MySQL Database
	protected static $database = 'netme';

	# @var, MySQL Username
	protected static $username = 'username';

	# @var, MySQL Password
	protected static $password = 'password';

	# @object, The PDO object
	private $pdo;

	# @object, PDO statement object
	private $sQuery;

	# @array,  The database settings
	private $settings;

	# @bool ,  Connected to the database
	private $bConnected = false;

	# @object, Object for logging exceptions	
	private $log;

	# @array, The parameters of the SQL query
	private $parameters;
		
	/**
	*   Default Constructor 
	*
	*	1. Connect to database.
	*	2. Creates the parameter array.
	*/
	public function __construct($hostname = '', $database = '', $username = '', $password = '')
	{ 	
		if(empty($hostname)){
			$hostname = self::$hostname;
		}
		if(empty($database)){
			$database = self::$database;
		}
		if(empty($username)){
			$username = self::$username;
		}
		if(empty($password)){
			$password = self::$password;
		}
		$this->Connect($hostname, $database, $username, $password);
		$this->parameters = array();
	}

			 /**
	*	This method makes connection to the database.
	*	
	*	1. Reads the database settings from a ini file. 
	*	2. Puts  the ini content into the settings array.
	*	3. Tries to connect to the database.
	*	4. If connection failed, exception is displayed.
	*/
	private function Connect($hostname, $database, $username, $password)
	{
		global $settings;
		$dsn = 'mysql:dbname='.$database.';host='.$hostname;
		try 
		{
			# Read settings from INI file, set UTF8
			$this->pdo = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			
			# We can now log any exceptions on Fatal error. 
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			# Disable emulation of prepared statements, use REAL prepared statements instead.
			$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
			
			# Connection succeeded, set the boolean to true.
			$this->bConnected = true;
		}
		catch (PDOException $e) 
		{
			# Write into log
			echo $this->ExceptionLog($e->getMessage());
			die();
		}
	}
	/*
	 *   You can use this little method if you want to close the PDO connection
	 *
	 */
	public function CloseConnection()
	{
		# Set the PDO object to null to close the connection
		# http://www.php.net/manual/en/pdo.connections.php
		$this->pdo = null;
	}
		
			 /**
	*	Every method which needs to execute a SQL query uses this method.
	*	
	*	1. If not connected, connect to the database.
	*	2. Prepare Query.
	*	3. Parameterize Query.
	*	4. Execute Query.	
	*	5. On exception : Write Exception into the log + SQL query.
	*	6. Reset the Parameters.
	*/	
	private function Init($query,$parameters = "")
	{
	# Connect to database
	if(!$this->bConnected) { $this->Connect(); }
	try {
			# Prepare query
			$this->sQuery = $this->pdo->prepare($query);
			
			# Add parameters to the parameter array	
			$this->bindMore($parameters);

			# Bind parameters
			if(!empty($this->parameters)) {
				foreach($this->parameters as $param)
				{
					$parameters = explode("\x7F",$param);
					$this->sQuery->bindParam($parameters[0],$parameters[1]);
				}		
			}

			# Execute SQL 
			$this->success = $this->sQuery->execute();		
		}
		catch(PDOException $e)
		{
				# Write into log and display Exception
				$this->ExceptionLog($e->getMessage(), $query );
		}

		# Reset the parameters
		$this->parameters = array();
	}
		
			 /**
	*	@void 
	*
	*	Add the parameter to the parameter array
	*	@param string $para  
	*	@param string $value 
	*/	
	public function bind($para, $value)
	{	
		$this->parameters[sizeof($this->parameters)] = ":" . $para . "\x7F" . utf8_encode($value);
	}
			 /**
	*	@void
	*	
	*	Add more parameters to the parameter array
	*	@param array $parray
	*/	
	public function bindMore($parray)
	{
		if(empty($this->parameters) && is_array($parray)) {
			$columns = array_keys($parray);
			foreach($columns as $i => &$column)	{
				$this->bind($column, $parray[$column]);
			}
		}
	}
			 /**
	*   	If the SQL query  contains a SELECT or SHOW statement it returns an array containing all of the result set row
	*	If the SQL statement is a DELETE, INSERT, or UPDATE statement it returns the number of affected rows
	*
	*   	@param  string $query
	*	@param  array  $params
	*	@param  int    $fetchmode
	*	@return mixed
	*/			
	public function query($query,$params = null, $fetchmode = PDO::FETCH_ASSOC)
	{
		$query = trim($query);

		$this->Init($query,$params);

		$rawStatement = explode(" ", $query);
		
		# Which SQL statement is used 
		$statement = strtolower($rawStatement[0]);
		
		if ($statement === 'select' || $statement === 'show') {
			return $this->sQuery->fetchAll($fetchmode);
		}
		elseif ( $statement === 'insert' ||  $statement === 'update' || $statement === 'delete' ) {
			return $this->sQuery->rowCount();	
		}	
		else {
			return NULL;
		}
	}
		
			/**
			 *  Returns the last inserted id.
			 *  @return string
			 */	
		public function lastInsertId() {
			return $this->pdo->lastInsertId();
		}	
		
			 /**
	*	Returns an array which represents a column from the result set 
	*
	*	@param  string $query
	*	@param  array  $params
	*	@return array
	*/	
	public function column($query,$params = null)
	{
		$this->Init($query,$params);
		$Columns = $this->sQuery->fetchAll(PDO::FETCH_NUM);		
		
		$column = null;

		foreach($Columns as $cells) {
			$column[] = $cells[0];
		}

		return $column;
		
	}	
			 /**
	*	Returns an array which represents a row from the result set 
	*
	*	@param  string $query
	*	@param  array  $params
	*   	@param  int    $fetchmode
	*	@return array
	*/	
	public function row($query,$params = null,$fetchmode = PDO::FETCH_ASSOC)
	{				
		$this->Init($query,$params);
		return $this->sQuery->fetch($fetchmode);			
	}
			 /**
	*	Returns the value of one single field/column
	*
	*	@param  string $query
	*	@param  array  $params
	*	@return string
	*/	
	public function single($query,$params = null)
	{
		$this->Init($query,$params);
		return $this->sQuery->fetchColumn();
	}
	 /**	
	* Writes the log and returns the exception
	*
	* @param  string $message
	* @param  string $sql
	* @return string
	*/
	private function ExceptionLog($message , $sql = "")
	{
		$exception  = 'Unhandled Exception. <br />';
		$exception .= $message;
		$exception .= "<br /> You can find the error back in the log.";

		if(!empty($sql)) {
			# Add the Raw SQL to the Log
			$message .= "\r\nRaw SQL : "  . $sql;
		}
		throw new Exception($message);
		#return $exception;
	}			
	
	 /**	
	* Secure data parameters
	*
	* @param  string $data
	* @return array
	*/
	public function SecureString($string){
		return htmlspecialchars(strip_tags($string), ENT_QUOTES, 'UTF-8');
	}
	
	 /**	
	* Check data parameters
	*
	* @param  array $data
	* @return array
	*/
	public function DecodeData($data){
		$_data = [];
		//Check terms
		if(isset($data->db_type) && ($data->db_type == "db_pmc" || $data->db_type == "db_pubmed")){
			if(!empty($data->db_terms)){
				$data->db_retmax = intval($data->db_retmax);
				if($data->db_retmax > 500)
					$data->db_retmax = 500;
				if($data->db_retmax < 1)
					$data->db_retmax = 1;	
				
				if(isset($data->db_type) && $data->db_type == "db_pmc"){
					$_data["pmc_terms"] = $this->SecureString($data->db_terms);
					$_data["pmc_sort"] = $data->db_sort == 'relevance' ? 'relevance' : 'pub+date';
					$_data["pmc_retmax"] = $data->db_retmax;
				}
				if(isset($data->db_type) && $data->db_type == "db_pubmed"){
					$_data["pubmed_terms"] = $this->SecureString($data->db_terms);
					$_data["pubmed_sort"] = $data->db_sort == 'relevance' ? 'relevance' : 'pub+date';
					$_data["pubmed_retmax"] = $data->db_retmax;
				}
			}
			else{
				if(isset($data->db_id) && !empty($data->db_id) && $data->db_id != "0"){
					$data->db_id = explode(",", $data->db_id);
					foreach($data->db_id as $key=>$value){
						if(intval($data->db_id[$key]) != 0)
							$data->db_id[$key] = intval($data->db_id[$key]);
					}
					$data->db_id = implode(",", $data->db_id);
					if(isset($data->db_type) && $data->db_type == "db_pmc"){
						$_data["pmc_id"] = $data->db_id;
					}
					if(isset($data->db_type) && $data->db_type == "db_pubmed"){
						$_data["pubmed_id"] = $data->db_id;
					}
				}
			}
		}else{
			if(isset($data->freetext) && !empty($data->freetext)){
				$_data["freetext"] = $this->SecureString($data->freetext);
			}
		}
		//Check other param
		if(isset($data->description) && !empty($data->description)){
			$_data["description"] = $this->SecureString($data->description);
		}else{
			$_data["description"] = "Network Generated on ".date('Y-m-d H:i:s');
		}
		
		return $_data;
	}
}
?>