<?php
/**
 *
 */

/**
 *
 */
abstract class Benchmark
{
	protected $bdd_name;
	protected $bdd;
	protected $test_id;
	protected $exists;
	protected $db_dir='db/';
	protected $iterations_stack=array();
	protected $connections_stack=array();

	private $called_class;
	private $tables_list=array();
	private $views_list=array();

// ------------------------------
// TABLES & TEMPLATES
// ------------------------------

	protected $tables = array();

	protected $masks = array(
		// db filenames
		'dbfile_mask' => '%s.sqlite',
		'dbfile_default_mask' => 'benchmark_%s.sqlite',
		'dbfile_mask_pcre' => '([a-zA-Z0-9_-]+).sqlite',
		// html
		'list'=>'%s',
		'list_html'=>'<table class="viewBenchmarkData">%s</table>',
		'list_head'=>"%s\n----------------------------\n",
		'list_head_row'=>"%s\n",
		'list_head_item'=>' %s |',
		'list_head_html'=>"<thead>%s</thead>\n",
		'list_head_row_html'=>'<tr>%s</tr>',
		'list_head_item_html'=>'<th>%s</th>',
		'list_body'=>"%s\n----------------------------\n",
		'list_body_row'=>"%s\n",
		'list_body_item'=>' %s |',
		'list_body_html'=>"<tbody>%s</tbody>\n",
		'list_body_row_html'=>'<tr>%s</tr>',
		'list_body_item_html'=>'<td>%s</td>',
	);

// ------------------------------
// INSTALL
// ------------------------------

	public function __construct( $filename=null, $options=null )
	{
		if (!empty($options)){
			foreach($options as $var=>$val){
				if (property_exists($this, $var)){
					if (is_array($this->$var))
						$this->$var = array_merge($this->$var, $val);
					else
						$this->$var = $val;
				}
			}
		}
		$this->setDbName( $filename );
		$this->called_class = get_class($this);
	}

	protected function _buildDbName( $filename=null )
	{
		if (!empty($filename))
			return $this->db_dir.sprintf($this->masks['dbfile_mask'], str_replace('.sqlite', '', $filename));
		else
			return $this->db_dir.sprintf($this->masks['dbfile_default_mask'], date('dmy'));
	}

	protected function _init()
	{
		if (isset($this->connections_stack[$this->bdd_name]) && $this->connections_stack[$this->bdd_name]===true)
			return true;
		if (!@file_exists($this->db_dir) || !@is_dir($this->db_dir) || !is_writable($this->db_dir)){
			trigger_error('Your database directory doesn\'t exist or is note writable ('.$this->db_dir.')!', E_USER_ERROR);
		}
		// list of defined tables
		if (!isset($this->tables_list[$this->called_class])){
			$this->tables_list[$this->called_class] = array();
			foreach($this->tables as $table=>$struct) {
				if (isset($struct['fields'])) {
					$this->tables_list[$this->called_class][] = $table;
				}
			}
		}
		// list of defined views
		if (!isset($this->views_list[$this->called_class])){
			$this->views_list[$this->called_class] = array();
			foreach($this->tables as $table=>$struct) {
				if (isset($struct['views'])) {
					$this->views_list[$this->called_class][$table] = array();
					foreach($struct['views'] as $view=>$view_str)
						$this->views_list[$this->called_class][$table][] = $view;
				}
			}
		}
		// verify that a 'test' table is defined
		if (!in_array('test', $this->tables_list[$this->called_class])){
			trigger_error(
				sprintf('You must at least define a table called \'test\' to write test infos (in class [%s])!', $this->called_class)
				, E_USER_ERROR);
		}
		$this->connections_stack[$this->bdd_name] = true;
	}

	protected function _install()
	{
		foreach($this->tables as $table=>$struct) {
			if (isset($struct['fields'])) {
				$schema='';
				foreach($struct['fields'] as $field=>$field_str)
					$schema .= "$field $field_str,";
				self::createTable( $table, rtrim($schema, ',') );
			}
			if (isset($struct['views'])) {
				foreach($struct['views'] as $view=>$view_str)
					self::createView( $view, $view_str );
			}
		}
	}

// ------------------------------
// SETTERS / GETTERS
// ------------------------------

	public function isInited()
	{
		return isset($this->connections_stack[$this->bdd_name]) ? $this->connections_stack[$this->bdd_name] : false;
	}
	
	public function isConnected()
	{
		return !empty($this->bdd);
	}
	
	public function getConnection( $filename=null, $transform=true )
	{
		if (!self::isConnected()){
			self::connect($filename);
		}
		return self::getDbName($transform);
	}
	
	public function setDbName( $filename=null )
	{
		$this->bdd_name = $this->_buildDbName( $filename );
	}

	public function getDbName( $transform=true )
	{
		if (true===$transform)
			return str_replace('.sqlite', '', str_replace($this->db_dir, '', $this->bdd_name));
		return $this->bdd_name;
	}

	public function getDbList()
	{
		$dbs = array();
		$dir = opendir($this->db_dir); 
		while($file = readdir($dir)) {
			if(preg_match('/^'.$this->masks['dbfile_mask_pcre'].'$/i', $file) && !is_dir($this->db_dir.$file)){
				$dbs[] = str_replace('.sqlite', '', $file);
			}
		}
		closedir($dir);
		return $dbs;
	}

	public function getDbDate( $filename )
	{
		return date('d-m-Y H:i:s', filemtime( $this->_buildDbName($filename) ));
	}

// ------------------------------
// BENCHMARK WRITE
// ------------------------------

	public function newTest( $testname, $description, $code='' )
	{
		$this->test_id = self::insertDataByLine(
			'test',
			array(
				'name'=>$testname,
				'description'=>$description,
				'code'=>$code,
				'date'=>date('d-m-Y H:i:s')
			)
		);
		return $this->test_id;
	}

	public function newTestData( $data )
	{
		$this->iteration_stack[] = array_merge($data, array(
			'test_id'=>$this->test_id
		));
	}

	public function saveTestData()
	{
		return self::insertTableData(
			'testdata', $this->iteration_stack
		);
	}

	public function deleteTestData( $id )
	{
		return self::deleteDataById($id);
	}

// ------------------------------
// BENCHMARK LOOKUP
// ------------------------------

	public function readTest( $testname )
	{
		return self::select( "SELECT * FROM test WHERE name='$testname';" );
	}

	abstract public function readTestView_Summarize( $viewname, $testname=null );

	abstract public function readTestView_AllData( $viewname, $testname );

// ------------------------------
// TEMPLATING
// ------------------------------

	abstract public function viewBenchmarksList( $type='list', $attrs='', $use='filename', $selected=null );
	
	abstract public function viewBenchmark( $data, $relations, $ref='max', $url_mask=false );

	abstract public function viewBenchmarkData( $data, $html=false, $delete_url_mask=false );

// ------------------------------
// SQLITE COMMONS
// ------------------------------

	protected function connect( $filename=null, $chmod=0644 )
	{
		if (!is_null($filename)) $this->setDbName( $filename );
		if (!self::isInited()) self::_init();
		$install = (!@file_exists($this->bdd_name));
		$this->bdd = new SQLiteDatabase($this->bdd_name, $chmod, $err);
		if ($err) trigger_error( "Can not connect or create SQLite database '$bddname' [$err]!", E_USER_ERROR ); 
		if (true===$install) self::_install();
		return true;
	}

	protected function select( $query )
	{
		if (empty($this->bdd)) self::connect();
		$results = $this->bdd->query($query, SQLITE_ASSOC, $err);
		if ($err) trigger_error( "Can not select from table '$tablename' [$err]!", E_USER_ERROR );
		return $results->fetchAll();
	}

	protected function createTable( $tablename, $schema )
	{
		if (empty($this->bdd)) self::connect();
		$query = "CREATE TABLE $tablename ($schema);";
		$results = $this->bdd->query($query, SQLITE_BOTH, $err);
		if ($err) trigger_error( "Can not create table '$tablename' [$err]!", E_USER_ERROR );
		return true;
	}

	protected function createView( $viewname, $view )
	{
		if (empty($this->bdd)) self::connect();
		$query = "CREATE VIEW $viewname AS $view;";
		$results = $this->bdd->query($query, SQLITE_BOTH, $err);
		if ($err) trigger_error( "Can not create view '$viewname' [$err]!", E_USER_ERROR );
		return true;
	}

	protected function insertTableData( $tablename, $data )
	{	
		$counter=0;
		foreach($data as $var=>$val) {
			if ($ok = self::insertDataByLine( $tablename, $val ))
				$counter++;
		}
		return $counter;
	}

	protected function insertDataByLine( $tablename, $data )
	{	
		if (empty($this->bdd)) self::connect();
		$fields = join(',', array_keys($data));
		$_values = array_values($data);
		foreach($_values as $i=>$val) {
			if (!is_numeric($val) && is_string($val)) 
				$_values[$i] = "'".sqlite_escape_string($val)."'";
		}
		$values = join(',', $_values);
		$query = "INSERT INTO $tablename ($fields) VALUES ($values);";
		$results = $this->bdd->query($query, SQLITE_ASSOC, $err);
		if ($err) trigger_error( "Can not insert data in table '$tablename' [$err]!", E_USER_ERROR );
		return $this->bdd->lastInsertRowid();
	}

	protected function countTests( $db_name=null )
	{
		if (!is_null($db_name)) self::connect($db_name);
		elseif (empty($this->bdd)) self::connect();
		$query = "SELECT COUNT(id) FROM test";
		$result = $this->bdd->singleQuery($query, true);
		if (empty($result) && 0!==$this->bdd->lastError()){
			$err = $this->bdd->lastError();
			trigger_error( "Can not count entries from table 'test' [$err]!", E_USER_ERROR );
		}
		return $result[0];
	}

	protected function deleteDataById( $id )
	{
		if (empty($id)) return null;
		if (empty($this->bdd)) self::connect();
		foreach($this->tables_list[$this->called_class] as $_tabl){
			$query = "DELETE FROM $_tabl WHERE id=$id";
			$results = $this->bdd->query($query, SQLITE_ASSOC, $err);
			if ($err) trigger_error( "Can not delete from table '$_tabl' [$err]!", E_USER_ERROR );
		}
		return true;
	}

}

// Endfile