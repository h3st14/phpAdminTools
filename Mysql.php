<?
/*
 * Mysql
 */
interface ___Database {
	public getTables();
	public tableDump();
}

/**
 * Framework to access mysql with.
 *
 * @author		Andrea Paterno'
 * @version		0.1
 */
class __Mysql implements ___Database {
	private $username;
	private $password;
	private $host;
	private $database;
	private $port = null;

	/**
	 * Connects to the database.
	 *
	 * @throws	__Exception		Couldn't connect to the database.
	 */
	private function connect() {
		$r = mysql_connect($this->host.(($this->port) ? ':'.$this->port : ''), $this->username, $this->password);

		if(!$r) {
			throw __Exception( __CLASS__, 0, 'Couldn\' connect to MySQL');
			return FALSE;
		}
		
		return TRUE;
	}

	/**
	 * Select the db.
	 *
	 * @throws	__Exception		Couldn't select the database.
	 */
	private function dbselect() {
		$r = mysql_select_db($this->database);

		if(!$r) {
			throw __Exception( __CLASS__, 1, 'Couldn\'t select the database' );
			return FALSE;
		}

		return TRUE;
	}
		
	function __construct($h, $u, $p, $n) {
		// Checks
		
		$this->host = $h;
		$this->username = $u;
		$this->password = $p;
		$this->port = $n;

		try {
			$this->connect();
		} catch ( __Exception e ) {
			print $e->toString();

			// Terminate execution
		}
	}

	/**
	 * List all the tables in the database.
	 *
	 * @return	Array list of the tables
	 */
	private function getTables() {
		$tables = array();
		$result = mysql_query('SHOW TABLES');
		while($row = mysql_fetch_row($result))
			$tables[] = $row[0];
		return $tables;
	}
	
	/**
	 * Dumps the contents of a table/more tables.
	 *
	 * @param	$table		Table to be dumped. '*' for all the tables.
	 * @param	$stream		Stream to put the contents in
	 */
	function tableDump($tables = '*', $stream) {
		if(!($stream instanceof ___Stream())) {
			throw __Exception( __CLASS__, 2, 'Stream not valid' );
		
		if($tables == '*') {
			$tables = $this->getTables();
		} else {
			$tables = is_array($tables) ? $tables : explode(',',$tables);
		}

		//cycle through
		foreach($tables as $table) {
			$result = mysql_query('SELECT * FROM '.$table);
			$num_fields = mysql_num_fields($result);

			$stream->pushln('DROP TABLE '.$table.';');
			
			$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
			$stream->push("\n\n".$row2[1].";\n\n");

			for ($i = 0; $i < $num_fields; $i++)  {
				while($row = mysql_fetch_row($result)) {
					$return = 'INSERT INTO '.$table.' VALUES(';
					
					for($j=0; $j<$num_fields; $j++) {
						$row[$j] = addslashes($row[$j]);
						$row[$j] = ereg_replace("\n","\\n",$row[$j]);
						
						$return .= (isset($row[$j])) ? '"'.$row[$j].'"' : '""';

						if($j<($num_fields-1))
							$return.= ',';
					}
					
					$stream->pushln($return.");");
				}
			}
			
			$stream->push("\n\n\n");
		}

		//$stream->flush();
	}
}

?>