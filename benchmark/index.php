<?php
/**
 */

// ------------------------------
// COMMONS
// ------------------------------

// show errors at least initially
ini_set('display_errors','1'); error_reporting(E_ALL ^ E_NOTICE);

// set a default timezone to avoid PHP5 warnings
$tmz = date_default_timezone_get();
date_default_timezone_set( !empty($tmz) ? $tmz : 'Europe/Paris' );

// ------------------------------
// CONFIG
// ------------------------------

require 'ExecutionTimeBenchmark.php';

$bench = new ExecutionTimeBenchmark;

$iterations = 100; 
$test_file = '../MD_syntax.md';

$relations = array(
	'title'=>'A1.name',
	'description'=>'A1.description',
	'value'=>'AVG(A2.exectime)',
);

$tests_set=array(
	'Markdown'=>array(
		'description'=>"\"Markdown\" initial PHP version, test on file '$test_file' (getting content and parsing for each iteration).",
		'content'=>"
				require_once '../PHP_Markdown_1.0.1o/markdown.php';
				\$md_content = \$file_content = null;
				\$file_content = file_get_contents('$test_file');
				\$md_content = Markdown( \$file_content );
		",
	),
	'Markdown_Extra'=>array(
		'description'=>"\"Markdown_Extra\" extended version, test on file '$test_file' (getting content and parsing for each iteration).",
		'content'=>"
				require_once '../PHP_Markdown_Extra_1.2.5/markdown.php';
				\$md_content = \$file_content = null;
				\$file_content = file_get_contents('$test_file');
				\$md_content = Markdown( \$file_content );
		",
	),
	'Markdown_Extended'=>array(
		'description'=>"\"Markdown_Extended\" NOT object-oriented version, test on file '$test_file' (getting content and parsing for each iteration).",
		'content'=>"
				require_once '../PHP_Extended_Markdown/markdown.php';
				\$md_content = \$file_content = null;
				\$file_content = file_get_contents('$test_file');
				\$md_content = Markdown( \$file_content );
		",
	),
	'OO_Markdown_Extended'=>array(
		'description'=>"\"Markdown_Extended OO\" object-oriented version, test on file '$test_file' (getting content and parsing for each iteration).",
		'content'=>"
				require_once '../OO_Extended_Markdown/markdown.php';
				\$md_content = \$file_content = null;
				\$file_content = file_get_contents('$test_file');
				\$md_content = Markdown( \$file_content );
		",
	),
);

// ------------------------------
// PROCESS
// ------------------------------

$ctt='';
$user_test = array(
	'name' => '',
	'iterations' => $iterations,
	'description' => '',
	'code' => '',
);

if (!empty($_POST)) {
//echo '<pre>';var_export($_POST);
	$user_test = array(
		'name' => $_POST['testname'],
		'iterations' => $_POST['iterations'],
		'description' => $_POST['testdescription'],
		'code' => stripslashes($_POST['testcode']),
	);
	$ctt .= '<p>Launching user test: '.$user_test['name'].' ('.$user_test['description'].') [ '.$user_test['iterations'].' iterations ]</p>';
	$bench->connect();
	$bench->newTest( $user_test['name'], $user_test['description'].' [ '.$user_test['iterations'].' iterations ]' );
	$toeval = $user_test['code'];

	$time_start = microtime(true);
	for ($i=0; $i<$iterations; $i++) {    
		$item_start = microtime(true);
		
		eval("$toeval");

		$item_end = microtime(true);
		$bench->newTestData( array(
			'iteration'=>$i,
			'begin' => $item_start,
			'end' => $item_end,
			'exectime' => ($item_end - $item_start),
		) );
	}
	$time_end = microtime(true);

	if ($bench->saveTestData()) $ctt .= '<p>OK - Test done</p>';
}

if (!empty($_GET) && isset($_GET['type'])) {
	switch($_GET['type']) {

		case 'run':
			$test = $_GET['test'];
			if (isset($tests_set[$test])) {
				$ctt .= '<p>Launching test 1: '.$tests_set[$test]['description'].' [ '.$iterations.' iterations ]</p>';
				$bench->connect();
				$bench->newTest( $test, $tests_set[$test]['description'].' [ '.$iterations.' iterations ]' );
				$toeval = $tests_set[$test]['content'];

				$time_start = microtime(true);
				for ($i=0; $i<$iterations; $i++) {    
					$item_start = microtime(true);
					
					eval("$toeval");

					$item_end = microtime(true);
					$bench->newTestData( array(
						'iteration'=>$i,
						'begin' => $item_start,
						'end' => $item_end,
						'exectime' => ($item_end - $item_start),
					) );
				}
				$time_end = microtime(true);

				if ($bench->saveTestData()) $ctt .= '<p>OK - Test done</p>';
			} else {
				$ctt .= '<p>Test '.$test.' not found!</p>';
			}
			break;

		case 'benchmark':
			if (isset($_GET['delete'])) {
				$bench->deleteTestData( $_GET['delete'] );
			}
			if (isset($_GET['test'])) {
				$data = $bench->readTestView_AllData( 'BENCHMARK', $_GET['test'] );
				if (!empty($data)) $ctt .= "<p>All data</p>".$bench->listView( $data, true );
			} else {
				$sumdata = $bench->readTestView_Summarize( 'BENCHMARK' );
				if (!empty($sumdata)) {
					$ref = isset($_GET['ref']) ? $_GET['ref'] : 'max';
					$ctt .= "<h2>Global benchmark results</h2>"
						."<p>Change reference used for graphs: <a href=\"index.php?type=benchmark&ref=max\">max</a> | <a href=\"index.php?type=benchmark&ref=min\">min</a></p>"
						.$bench->benchmarkView( $sumdata, $relations, $ref, 'index.php?type=benchmark&ref=%s' )
						."<h2>Summarize</h2>"
						.$bench->listView( $sumdata, true, 'index.php?type=benchmark&delete=%s' );
				}
			}
			break;

		default:break;
	}
} else {

	$test_list = '';
	foreach($tests_set as $test=>$content) {
		$test_url = 'index.php?type=run&test='.urlencode($test);
		$test_list .= '<li>'
			.'<strong><a href="'.$test_url.'">'.$test.'</a></strong>'
			.'<br />'.$content['description']
			.'<br />Test to tun on '.$iterations.' iterations'
			.'</li>';
	}

	$ctt .= <<<EOT
<h2>Run a test-set</h2>
<ul>
	{$test_list}
</ul>
<h2>Build a new test-set</h2>
<form action="" method="post">
<fieldset>
	<legend>Build a new test</legend>

	<p>	
		<label for="testname">Name of the test</label>
		<input type="text" name="testname" id="testname" value="{$user_test['name']}" />
	</p>
	
	<p>	
		<label for="iterations">Number of iterations</label>
		<input type="text" name="iterations" id="iterations" maxlength="10" size="10" value="{$user_test['iterations']}" />
	</p>

	<p>	
		<label for="testdescription">Description of the test</label>
		<textarea name="testdescription" id="testdescription" cols="12" rows="2">{$user_test['description']}</textarea>
	</p>
	
	<p>	
		<label for="testcode">Test content</label>
		<textarea name="testcode" id="testcode" cols="12" rows="6">{$user_test['code']}</textarea>
		<span class="comment">PHP code to execute for each iteration.</span>
	</p>
	
</fieldset>

	<input type="submit" value="Run this test" />
	<input type="reset" />
	
</form>
EOT;

}

// ------------------------------
// VIEW
// ------------------------------

	$test_menu = '';
	foreach($tests_set as $test=>$content) {
		if (!isset($content['menu']) || $content['menu']!==false) {
			$test_url = 'index.php?type=run&test='.urlencode($test);
			$test_menu .= '<li><a href="'.$test_url.'">'.$test.'</a></li>';
		}
	}

	echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Benchmark Markdown parser</title>
	<style>
body {
	font-size: 0.8125em; font-family: Lucida Grande, Verdana, Sans-serif; 
	background: #fff; padding: 0; margin: 0; color: #4F5155; }
ul           { padding: 0 0 0 20px; }
h1           { font-size: 160%; }
h2           { font-size: 140%; }
h3           { font-size: 120%; }
div, span, p { padding:0; margin: 0; }
ol, ul       { padding:0; margin: 0; margin-left: 15px; }
li           { padding:0; margin: 0; padding-left: 5px; margin-bottom: 8px; text-indent: 0; }
ul li        { margin-left: 15px; list-style-type: disc; }
ol li        { margin-left: 15px; }
a            { padding:0; margin: 0; text-decoration: none; font-size: inherit;}
img          { border: 0; margin: .2em; }
fieldset     { margin: 12px 1em; width: 96%; }
textarea     { width: 96%; height: 60%; min-height: 400px; margin: 12px 1em; padding: 8px; }
h1           { color: #444; font-weight: bold; margin: 36px 10px; padding: 0;}
h2           { margin: 20px 0 10px 0; padding: 0; font-weight: bold; border-bottom: 1px solid #cccccc; line-height: 1.4em; }
a            { color: #003399; }
a:hover      { color: #7A63AA; }
table        {  }
table th     { padding: 6px; border: 1px dotted #ccc; }
table td     { padding: 6px; border: 1px dotted #ccc; }

p, blockquote, ul, ol, dl, li, table, pre
             { margin: 1em 0; font-size: 14px; }
h1 + p, h2 + p, h3 + p, h4 + p, h5 + p, h6 + p
             { margin-top: 0; text-indent:1em; }

code, cite, pre { font-family: Monaco, Verdana, Sans-serif; background-color: #f9f9f9; border: 1px solid #D0D0D0; color: #002166; font-size: 12px; text-indent:0; }
code            { padding: 0 .6em; display: inline; margin:0; }
cite            { font-size: .9em; display: block; margin: 1em; padding: 0; padding-left: 2em; }
blockquote      { font-size: .9em; display: block; margin: 1em; padding: 0; padding-left: 2em; border: none; border-left: 2px solid #ddd; }
pre             { font-size: 12px; display: block; margin: 1em 0; padding: .6em; overflow:auto; max-height:320px; }
pre code        { border: none; text-indent:0; padding: 0; }

form { margin: 12px; }
fieldset { border: 1px dotted #ddd; }
label { display: inline-block; width: auto; min-width: 160px; vertical-align: top; }
textarea { margin: 0; width:auto !important; min-width: 280px; height:auto !important; min-height: 80px; }

#wrapper     { margin: 0 1em; min-height: 100%; padding: 10px; position: relative; }
.clear       { clear: both; }
.comment     { font-size: .9em; }

.benchmark_graph             { float: left; display: inline-block; position: relative; margin: 2px; margin-left: 40px; width: 400px; height: 26px; border: 1px solid #ccc; background-color: #eee; }
.benchmark_ingraph           { height: 26px; display: inline-block; text-align: right; color: #fff; }
.benchmark_outgraph          { float: left; text-align: left; }
.benchmark_ingraph.colored_0 { background-color: #FFCC66; }
.benchmark_ingraph.colored_1 { background-color: #99CC66; }
.benchmark_ingraph.colored_2 { background-color: #3399CC; }
.benchmark_ingraph.colored_3 { background-color: #00FFFF; }
.benchmark_ingraph.colored_4 { background-color: #33FFFF; }

#navigation_menu {
	display: block; text-align:left; background: #eee; width:100%; height:20px; margin:0 auto 20px auto; padding: 4px; }
#navigation_menu ul          { padding:0; margin:0; list-style-type:none; }
#navigation_menu ul li       { padding:0; margin:0 10px; float:left; position:relative; list-style-type:none; width: auto; line-height:20px; }
#navigation_menu ul li ul    { padding:0; margin:0; visibility:hidden; position:absolute; top:0; left:0; height:0; overflow:hidden; }
#navigation_menu ul li ul li { padding:4px; margin:0; list-style-type: none; line-height:20px; height: 20px; width: auto; min-width: 100px; }
#navigation_menu a, #navigation_menu a:visited {
	display:inline-block; text-decoration:none; padding:0; margin:0; }
#navigation_menu li li a, #navigation_menu li li a:visited { font-size 0.96em; line-height: 20px; }
#navigation_menu li.first { font-weight: bold; width: auto !important; }
#navigation_menu a:hover { color:#fff; text-decoration:none; border:0; }
#navigation_menu li.submenu:after { content: ' >>';}
#navigation_menu ul li:hover ul, #navigation_menu ul li a:hover ul {
	visibility:visible; width:auto; height:auto; position:absolute; top:24px; left:-4px; 
	background:#eee; border:1px solid #fff; overflow:visible; z-index:10; }
	</style>
	<script type="text/javascript"><!--//
function emdreminders_popup(url){
	if (!url) url='markdown_reminders.html?popup';
	if (url.lastIndexOf("popup")==-1) url += (url.lastIndexOf("?")!=-1) ? '&popup' : '?popup';
	var new_f = window.open(url, 'markdown_reminders', 
       'directories=0,menubar=0,status=0,location=1,scrollbars=1,resizable=1,fullscreen=0,width=840,height=380,left=120,top=120');
	new_f.focus();
	return false; 
}
	//--></script>
</head>
<body>
	<div id="wrapper">
	<h1>Benchmark of Markdown execution</h1>
	<div id="navigation_menu">
		<ul>
			<li class="first"><strong>MENU</strong></li>
			<li><a href="index.php">Home</a></li>
			<li class="submenu"><a href="#" title="Choose a test to run">Tests</a>
			<ul>
				{$test_menu}
			</ul>
			</li>
			<li><a href="index.php?type=benchmark">Analyze benchmark data</a></li>
			<li><a href="../PHP_Extended_Markdown/markdown_reminders.html" onclick="return emdreminders_popup('../PHP_Extended_Markdown/markdown_reminders.html');" title="Markdown syntax reminders (new floated window)" target="_blank">
				Markdown syntax reminders</a></li>
		</ul>
	</div>
	{$ctt}
	</div>
</body>
</html>
EOT;
	exit;

// Endfile