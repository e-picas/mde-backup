<?php
/**
 *
 */

/**
 *
 */
class ExecutionTimeBenchmark extends Benchmark
{

// ------------------------------
// TABLES & TEMPLATES
// ------------------------------

	protected $tables = array(

		'test'=>array(
			'fields'=>array(
				'id'=>'INTEGER PRIMARY KEY ASC',
				'name'=>'VARCHAR(255) NOT NULL',
				'description'=>'VARCHAR(255) NOT NULL',
				'code'=>'VARCHAR(2000) NOT NULL',
				'date'=>'DATETIME',
			),
			'views'=>array(
				'TEST_INFOS'=>"SELECT id, name, description, code, date FROM test"
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
				'BENCHMARK'=> "SELECT test.name, test.description, test.code, test.date, 
                    testdata.iteration, testdata.begin, testdata.end, testdata.exectime 
					FROM test, testdata
					WHERE testdata.test_id = test.id",
			),
		),
	);

	protected $executiontime_masks = array(
		'dbfile_default_mask' => 'exectime_benchmark_%s.sqlite',
	);

// ------------------------------
// INSTALL
// ------------------------------

	public function __construct( $filename=null, $options=null )
	{
		parent::__construct(
			$filename,
			array_merge_recursive(array(
				'masks'=>$this->executiontime_masks
			), $options)
		);
	}

// ------------------------------
// BENCHMARK LOOKUP
// ------------------------------

	public function readTestView_Summarize( $viewname, $testname=null )
	{
		return self::select(
			"SELECT A1.id, A1.name, A1.description, A1.date, 
      		MIN(A2.begin), MAX(A2.end), AVG(A2.exectime) 
			FROM test A1, testdata A2
			WHERE A2.test_id = A1.id ".( !empty($testname) ? "AND A1.name='$testname'": '')."
			GROUP BY A2.test_id;"
		);
	}

	public function readTestView_AllData( $viewname, $testname )
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

	public function viewBenchmarksList( $type='list', $attrs='', $use='filename', $selected=null )
	{
		$html=true;
		$bench_list = $this->getDbList();
		$str='';
		if (!empty($bench_list)){
			if ($type==='select'){
				$str .= '<select name="benchmark_db" '.$attrs.'>';
				foreach($bench_list as $name){
					if($use==='filename') $value = $name;
					$str .= '<option value="'.$value.'"'
						.( !is_null($selected) && $selected==$value ? ' selected="selected"' : '' )
						.'>'.str_replace('_', ' ', $name).'</option>';
				}
				$str .= '</select>';

			} elseif ($type==='table'){
				$list_table='';

				$heads = array( 'Name', 'Date', 'Number of Tests' );
				$head_str='';
				foreach($heads as $head) {
					$head_str .= sprintf( $this->masks['list_head_item'.($html?'_html':'')], $head );
				}
				$head_str = sprintf( $this->masks['list_head_row'.($html?'_html':'')], $head_str );
				$list_table .= sprintf( $this->masks['list_head'.($html?'_html':'')], $head_str );
				
				$body_str='';
				foreach($bench_list as $i=>$item) {
					$row_str='';
					foreach($heads as $head) {
						if ('Name'==$head){
							$row_str .= sprintf( $this->masks['list_body_item'.($html?'_html':'')], 
								'<a href="'.sprintf($attrs, $item).'" title="See this test">'.$item.'</a>'
							);
						} elseif ('Date'==$head){
							$row_str .= sprintf( $this->masks['list_body_item'.($html?'_html':'')], 
								$this->getDbDate($item)
							);

						} elseif ('Number of Tests'==$head){
							$row_str .= sprintf( $this->masks['list_body_item'.($html?'_html':'')], 
								$this->countTests($item)
							);
						}
					}
					$body_str .= sprintf( $this->masks['list_body_row'.($html?'_html':'')], $row_str );
				}
				$list_table .= sprintf( $this->masks['list_body'.($html?'_html':'')], $body_str );

				$str .= sprintf( $this->masks['list'.($html?'_html':'')], $list_table );		
			}
		}
		return $str;
	}
	
	public function viewBenchmark( $data, $relations, $ref='max', $url_mask=false )
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

	public function viewBenchmarkData( $data, $html=false, $delete_url_mask=false )
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

}

// Endfile