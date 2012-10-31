<?php

class ExecutionTimeBenchmark
{

	protected $bdd;
	protected $test_id;
	protected $exists;
	static $db_dir='db/';

	protected $iterations_stack=array();

// ------------------------------
// TABLES & TEMPLATES
// ------------------------------

	private $tables = array(

		'test'=>array(
			'fields'=>array(
				'id'=>'INTEGER PRIMARY KEY ASC',
				'name'=>'VARCHAR(255) NOT NULL',
				'description'=>'VARCHAR(255) NOT NULL',
				'date'=>'DATETIME',
			),
			'views'=>array(
				'TEST_INFOS'=>"SELECT id, name, description, date FROM test"
			),
		),

		'testdata'=>array(
			'fields'=>array(
				'id'=>'INTEGER PRIMARY KEY ASC',
				'test_id'=>'INTEGER',
				'iteration'=>'INTEGER NOT NULL',
				'begin'=>'INTEGER',
				'end'=>'INTEGER',
				'exectime'=>'FLOAT(4)',
			),
			'views'=>array(
				'TEST_DATA'=>"SELECT id, test_id, iteration, begin, end, exectime FROM testdata"
			)
		),

		'benchmark'=>array(
			'views'=>array(
				'BENCHMARK'=> "SELECT test.name, test.description, test.date, 
                         testdata.iteration, testdata.begin, testdata.end, testdata.exectime 
											FROM test, testdata
											WHERE testdata.test_id = test.id",
			),
		),
	);

	var $masks = array(
		'list'=>'%s',
		'list_html'=>'<table class="listView">%s</table>',
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

	function __construct( $filename=null, $deletetest_link_mask=null )
	{
		if (!empty($filename))
			$this->bdd_name = self::$db_dir.str_replace('.sqlite', '', $filename).'.sqlite';
		else
			$this->bdd_name = self::$db_dir.'benchmark_'.date('dmy').'.sqlite';
		self::_init();
	}

	protected function _init()
	{
		if (!@file_exists($this->bdd_name)) self::install();
	}

	function install()
	{
		self::connect();
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
// BENCHMARK LOOKUP
// ------------------------------

	function readTest( $testname )
	{
		return self::select( "SELECT * FROM test WHERE name='$testname';" );
	}

	function readTestView_Summarize( $viewname, $testname=null )
	{
		return self::select(
			"SELECT A1.id, A1.name, A1.description, A1.date, 
      		MIN(A2.begin), MAX(A2.end), AVG(A2.exectime) 
			FROM test A1, testdata A2
			WHERE A2.test_id = A1.id ".( !empty($testname) ? "AND A1.name='$testname'": '')."
			GROUP BY A2.test_id;"
		);
	}

	function readTestView_AllData( $viewname, $testname )
	{
		return self::select(
			"SELECT A1.name, A1.description, A1.date, 
      		A2.iteration, A2.begin, A2.end, A2.exectime 
			FROM test A1, testdata A2
			WHERE A1.name='$testname' AND A2.test_id = A1.id;"
		);
	}

// ------------------------------
// TEMPLATING
// ------------------------------

	function benchmarkView( $data, $relations, $ref='max', $url_mask=false )
	{
		if (empty($data)) return '';
		if (empty($relations['title'])) trigger_error( "No 'title' relation setted!", E_USER_ERROR );

		$grouped_results = array();		
		foreach($data as $i=>$item) {
			$title = $item[$relations['title']];
			if (!isset($grouped_results[$title])) {
				$grouped_results[$title] = array('counter'=>1);
				$grouped_results[$title]['value'] = $item[$relations['value']];
				if (isset($relations['description']))
					$grouped_results[$title]['description'] = $item[$relations['description']];
			} else {
				$grouped_results[$title]['counter']++;
				$grouped_results[$title]['value'] = 
				 ($grouped_results[$title]['value'] + $item[$relations['value']]) / 2;
			}
		}

		foreach($grouped_results as $title=>$results) {
			if (!isset($max_value)) $max_value = $results['value'];
			elseif ($results['value']>$max_value) $max_value = $results['value'];
			if (!isset($min_value)) $min_value = $results['value'];
			elseif ($results['value']<$min_value) $min_value = $results['value'];
		}

		switch($ref) {
			case 'min': $reference = $min_value; break;
			case 'max': $reference = $max_value; break;
			default: 
				if (isset($grouped_results[$ref])) $reference = $grouped_results[$ref]['value']; 
				else $reference = $max_value;
				break;
		}

		$colored_class=0;
		$content='';
		foreach($grouped_results as $title=>$results) {
			$content .= "<p><strong>$title</strong>".( isset($results['description']) ? ' - '.$results['description'] : '' )."</p>";
			$val = round($results['value'], 3);
			$graph_width = 400 * (  $results['value'] * 100 / $max_value ) / 100;
			$percent = round($results['value'] * 100 / $reference);
			$ref = $results['value']==$reference ? '<strong title="This test had been used at reference (100%)">ref.</strong>' : '';
			$change_ref = '';
			if ($url_mask && $results['value']!=$reference) {
				$_url = sprintf($url_mask, urlencode($title));
				$change_ref = '<a href="'.$_url.'" title="Use this value as reference">use as ref.</a>';
			}
			$content .= <<<EOT
<div class="benchmark_graph">
	<div class="benchmark_ingraph colored_{$colored_class}" style="width: {$graph_width}px;">{$val} s.&nbsp;</div>
</div>
<div class="benchmark_outgraph">{$ref} {$percent} % {$change_ref}</div>
<br class="clear" />
EOT;
			$colored_class++;
		}

		return $content;		

echo '<pre>';
var_export($grouped_results);
var_export($content);
exit('yo');
	}

	function listView( $data, $html=false, $delete_url_mask=false )
	{
		if (empty($data)) return '';
		$list_table='';
		
		$heads = array_keys( $data[0] );
		$head_str='';
		foreach($heads as $head) {
			if ('A1.id'==$head && !empty($delete_url_mask)){
				$head_str .= sprintf( $this->masks['list_head_item'.($html?'_html':'')], 'Delete' );
			} elseif ('A1.id'!=$head){
				$head_str .= sprintf( $this->masks['list_head_item'.($html?'_html':'')], $head );
			}
		}
		$head_str = sprintf( $this->masks['list_head_row'.($html?'_html':'')], $head_str );
		$list_table .= sprintf( $this->masks['list_head'.($html?'_html':'')], $head_str );
				
		$body_str='';
		foreach($data as $i=>$item) {
			$row_str='';
			foreach($heads as $head) {
				if ('A1.id'==$head && !empty($delete_url_mask)){
					$deleteitem_url = sprintf($delete_url_mask, $item[$head]);
					$row_str .= sprintf( $this->masks['list_body_item'.($html?'_html':'')], 
						'<a href="'.$deleteitem_url.'" title="Delete this set of test">-</a>'
					);
				} elseif ('A1.id'!=$head){
					$row_str .= sprintf( $this->masks['list_body_item'.($html?'_html':'')], 
						isset($item[$head]) ? $item[$head] : ''
					);
				}
			}
			$body_str .= sprintf( $this->masks['list_body_row'.($html?'_html':'')], $row_str );
		}
		$list_table .= sprintf( $this->masks['list_body'.($html?'_html':'')], $body_str );

		return sprintf( $this->masks['list'.($html?'_html':'')], $list_table );		
	}

// ------------------------------
// BENCHMARK WRITE
// ------------------------------

	function newTest( $testname, $description )
	{
		$this->test_id = self::insertDataByLine(
			'test',
			array(
				'name'=>$testname,
				'description'=>$description,
				'date'=>date('d-m-Y H:i:s')
			)
		);
		return $this->test_id;
	}

	function newTestData( $data )
	{
		$this->iteration_stack[] = array_merge($data, array(
			'test_id'=>$this->test_id
		));
	}

	function saveTestData()
	{
		return self::insertTableData(
			'testdata', $this->iteration_stack
		);
	}

	function deleteTestData( $id )
	{
		return self::deleteDataById($id);
	}

// ------------------------------
// COMMONS
// ------------------------------

	function connect( $chmod=0644 )
	{
	  $this->bdd = new SQLiteDatabase($this->bdd_name, $chmod, $err);
		if ($err) trigger_error( "Can not connect or create SQLite database '$bddname' [$err]!", E_USER_ERROR ); 
		return true;
	}

	function createTable( $tablename, $schema )
	{
		if (empty($this->bdd)) self::connect();
		$query = "CREATE TABLE $tablename ($schema);";
		$results = $this->bdd->query($query, $err);
		if ($err) trigger_error( "Can not create table '$tablename' [$err]!", E_USER_ERROR );
		return true;
	}

	function createView( $viewname, $view )
	{
		if (empty($this->bdd)) self::connect();
		$query = "CREATE VIEW $viewname AS $view;";
		$results = $this->bdd->query($query, $err);
		if ($err) trigger_error( "Can not create view '$viewname' [$err]!", E_USER_ERROR );
		return true;
	}

	function insertTableData( $tablename, $data )
	{	
		$counter=0;
		foreach($data as $var=>$val) {
			if ($ok = self::insertDataByLine( $tablename, $val ))
				$counter++;
		}
		return $counter;
	}

	function insertDataByLine( $tablename, $data )
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
		$results = $this->bdd->query($query, $err);
		if ($err) trigger_error( "Can not create table '$tablename' [$err]!", E_USER_ERROR );
		return $this->bdd->lastInsertRowid();
	}

	function select( $query )
	{
		if (empty($this->bdd)) self::connect();
		$results = $this->bdd->query($query, $err);
		if ($err) trigger_error( "Can not select from table '$tablename' [$err]!", E_USER_ERROR );
		return $results->fetchAll();
	}

	function deleteDataById( $id )
	{
		if (empty($this->bdd)) self::connect();
		$query = "DELETE FROM test WHERE id=$id";
		$results = $this->bdd->query($query, $err);
		if ($err) trigger_error( "Can not delete from table '$tablename' [$err]!", E_USER_ERROR );
		$query = "DELETE FROM testdata WHERE test_id=$id";
		$results = $this->bdd->query($query, $err);
		if ($err) trigger_error( "Can not delete from table '$tablename' [$err]!", E_USER_ERROR );
		return true;
	}

}

// Endfile