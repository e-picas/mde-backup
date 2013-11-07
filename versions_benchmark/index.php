<?php
/**
 */

// ------------------------------
// COMMONS
// ------------------------------

// show errors at least initially
ini_set('display_errors','1'); error_reporting(E_ALL);

// start time
$_exec_start_time = microtime();

// set a default timezone to avoid PHP5 warnings
$tmz = date_default_timezone_get();
date_default_timezone_set( !empty($tmz) ? $tmz : 'Europe/Paris' );

// the 'data' directory
define('BENCH_DATADIR', __DIR__.'/data/');

// the 'presets' DB
define('BENCH_PRESETS', BENCH_DATADIR.'test_presets.php');

// debug mode (?)
//define('_MODE', 'debug');

// ------------------------------
// CONFIG
// ------------------------------

require_once 'Benchmark.class.php';
require_once 'ExecutionTimeBenchmark.class.php';
$bench = new ExecutionTimeBenchmark( null, array(
	'db_dir'=>BENCH_DATADIR
));

$iterations = 100; 
$test_file = __DIR__.'/../../test/MD_syntax.md';

$relations = array(
	'title'=>'A1.name',
	'description'=>'A1.description',
	'value'=>'AVG(A2.exectime)',
);

// ------------------------------
// FUNCTIONS
// ------------------------------

function _get( $v, $def=null ){
	return !empty($_GET) && isset($_GET[$v]) ? $_GET[$v] : $def;
}

function _post( $v, $def=null ){
	return !empty($_POST) && isset($_POST[$v]) ? $_POST[$v] : $def;
}

function _execution_time( $start_time, $end_time ){
	$_s_t = explode(' ',$start_time);
	$_e_t = explode(' ',$end_time);
	return number_format( ($_e_t[1] + $_e_t[0]) - ($_s_t[1] + $_s_t[0]), 4 ); 
}

function getDbSelect( $bench, $selected=null ){
	return "<p>Visualize different benchmark:<br />"
		.$bench->viewBenchmarksList( 'select', ' onchange="seeOtherBenchnmark(this.options[this.selectedIndex].value);"', 'filename', $selected )
		."</p>";
}

function getDbInput( $default=null ){
	if (is_null($default)) $default = date('dmy');
	return "<p>Current working benchmark:<br />"
		.'<input type="text" name="db" value="'.$default.'" />'
		.'<input type="submit" value="ok" />'
		."</p>";
}

function buildShowHideBlock( $ctt, $handler, $closed=false, $id=null ){
	if (is_null($id)) $id = uniqid();
	return '<a name="'.$id.'_hd"></a>'
		.'<h2><a href="javascript:show_hide(\''.$id.'\');" title="Show/Hide this block" class="show_hide_handler">+/-</a>'
		.$handler.'</h2><div id="'.$id.'"'
		.( true===$closed ? ' style="visibility:hidden; display:none;"' : ' style="visibility:visible;"')
		.'>'.$ctt.'</div>';
}

function writePresets( $presets_list ){
	$f = @fopen(BENCH_PRESETS, 'w');
	$presets_list_str = var_export($presets_list, true);
	$intro = '# file generated at '.date('d-m-Y H:i:s');
	$f_ctt = <<<EOT
<?php
{$intro}
\$tests_presets={$presets_list_str};

EOT;
	$f_ctt .= '?>';
	if ($f) {
		fputs($f, $f_ctt);
		fclose($f);
		return true;
	}
	return false;
}

function readPresets(){
	$tests_presets=null;
	include BENCH_PRESETS;
	return $tests_presets;
}

function writePreset( $data ){
	$tests_presets = readPresets();
	foreach($data as $var=>$val){
		$data[$var] = stripslashes( $val );
	}
	$tests_presets[] = $data;
	return writePresets( $tests_presets );
}

function readPreset( $i ){
	$tests_presets = readPresets();
	if (isset($tests_presets[$i])) return $tests_presets[$i];
	return null;
}

function deletePreset( $i ){
	$tests_presets = readPresets();
	if (isset($tests_presets[$i])) unset($tests_presets[$i]);
	return writePresets($tests_presets);
}

function writeError( $fieldname, $errors=array() ){
	if (isset($errors[$fieldname])){
		return ' class="error"';
	}
	return '';
}

function getVersionsList() {
    $versions_dir = __DIR__.'/../versions/';
    $md_interface = 'markdown.php';
    $all_versions = array('current');
    foreach(scandir($versions_dir) as $_d) {
        if (!in_array($_d, array('.', '..')) && is_dir($versions_dir.$_d) && file_exists($versions_dir.$_d.'/'.$md_interface))
            $all_versions[] = $_d;
    }
    return $all_versions;
}
			
// ------------------------------
// PROCESS
// ------------------------------

$header_tool='';
$ctt='';
$console_ctt=array();
$user_test = array(
	'name' => '',
	'iterations' => $iterations,
	'description' => '',
	'code' => '',
);

if ($bdb = _post('db')) {
	$bench->getConnection( $bdb );
} elseif ($bdb = _get('db')) {
	$bench->getConnection( $bdb );
} else {
	$bdb = $bench->getConnection();
}

$form_errors=array();
if (!empty($_POST)) {
//echo '<pre>';var_export($_POST);
	// posted values
	$user_test = array(
		'name' => _post('testname'),
		'iterations' => _post('iterations'),
		'description' => _post('testdescription'),
		'code' => stripslashes(_post('testcode')),
	);
	$save_preset = _post('save_as_preset', 'off');
	$dry_run = _post('dry_run', 'off');
	$version_test = _post('version_test', false);

    if ($version_test) {
        $tested_version = _post('tested_version');
    	$user_test['name'] = 'Classic test of '.$tested_version.' version';
    	if ($tested_version==='current') {
    		$user_test['description'] = 'Current development PHP version, test on file \'$test_file\' (getting content and parsing for each iteration).';
	    	$user_test['code'] = str_replace('versions/ # VERSION # /', '/src/', $user_test['code']);
    	} else {
    		$user_test['description'] = '"'.$tested_version.'" PHP version, test on file \'$test_file\' (getting content and parsing for each iteration).';
	    	$user_test['code'] = str_replace('/ # VERSION # /', '/'.$tested_version.'/', $user_test['code']);
    	}
    }

	// check for errors
	if (empty($user_test['name'])) $form_errors['name'] = 'This field is required';
	if (empty($user_test['iterations'])) $form_errors['iterations'] = 'This field is required';
	if (empty($user_test['code'])) $form_errors['testcode'] = 'This field is required';

	// let's go
	if (0===count($form_errors)){
		if ($save_preset==='on'){
			writePreset( $user_test );
			$console_ctt[] = '> Test is recoded as a new preset.';
		}
		if ($dry_run==='on'){
			$console_ctt[] = '> Dry run of test: '.$user_test['name'].' ('.$user_test['description'].')';
			$toeval = $user_test['code'];
			try{
				ob_start();
				eval("$toeval");
				$console_ctt[] = ob_get_contents();
				ob_end_clean();
				$console_ctt[] = '> OK - Test seems working';
			} catch(Exception $e) {
				$console_ctt[] = '> ERROR - Test seems not working! (an exception has been catched)';
			}
	
		} else {
			$console_ctt[] = '> Launching user test: '.$user_test['name'].' ('.$user_test['description'].') [ '.$user_test['iterations'].' iterations ]';
			$bench->newTest( 
				$user_test['name'], 
				$user_test['description'].' [ '.$user_test['iterations'].' iterations ]',
				addslashes($user_test['code'])
			);
			$toeval = $user_test['code'];
		
			ob_start();
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
			ob_end_clean();
		
			if ($bench->saveTestData()) $console_ctt[] = '> OK - Test done';
		}
	} else {
		$console_ctt[] = 'FORM ERROR > Some required fields seems to be empty!';
	}
}

if ($_type = _get('type')) {
	switch($_type) {

		case 'testcreate':
			if (null!==$_pid = _get('preset', null)) {
				$user_test = readPreset( $_pid );
			} else {
				foreach($user_test as $var=>$val){
					$user_test[$var] = stripslashes( $val );
				}
			}
			$header_tool .= getDbInput( isset($bdb) ? $bdb : null );
			$req_str = ' <span class="required" title="Required field">*</span>';
			$save_check = _post('save_as_preset', 'off')==='on' ? ' checked="checked"' : '';
			$dry_check = _post('dry_run', 'off')==='on' ? ' checked="checked"' : '';
			$_errorHandler = 'writeError';
			$ctt .= <<<EOT
<h2>Build a new test-set</h2>
<form action="index.php?type=testcreate" method="post">
<fieldset>
	<legend>Build a new test</legend>

	<p>	
		<label for="testname">Name of the test{$req_str}</label>
		<input type="text" name="testname" id="testname" value="{$user_test['name']}"{$_errorHandler('name', $form_errors)} />
	</p>
	
	<p>	
		<label for="iterations">Number of iterations{$req_str}</label>
		<input type="text" name="iterations" id="iterations" maxlength="10" size="10" value="{$user_test['iterations']}"{$_errorHandler('iterations', $form_errors)} />
	</p>

	<p>	
		<label for="testdescription">Description of the test</label>
		<textarea name="testdescription" id="testdescription" cols="12" rows="2">{$user_test['description']}</textarea>
	</p>
	
	<p>	
		<label for="testcode">Test content{$req_str}</label>
		<textarea name="testcode" id="testcode" cols="12" rows="6"{$_errorHandler('testcode', $form_errors)}>{$user_test['code']}</textarea>
		<span class="comment">PHP code to execute for each iteration.</span>
	</p>
	
	<p>	
		<label>Tools</label>
		<label for="save_as_preset">
			<input type="checkbox" name="save_as_preset" id="save_as_preset"{$save_check} />
			Save as preset
		</label>
		<label for="dry_run">
			<input type="checkbox" name="dry_run" id="dry_run"{$dry_check} />
			Dry run (<em>run the test just once to test its validity - nothing will be recorded</em>)
		</label>
	</p>
	
</fieldset>

	<input type="submit" value="Run this test" />
	<input type="reset" />
	
</form>
EOT;
			break;

		case 'testsversions':
			$test_list = '';
			$header_tool .= getDbInput( isset($bdb) ? $bdb : null );
			$tests_list = getVersionsList();
            $opts='';
            foreach($tests_list as $_vers) {
                $opts .= '<option value="'.$_vers.'">'.$_vers.'</option>';
            }
            $selector = '<select name="tested_version">'.$opts.'</select>';
			$ctt .= <<<EOT
<h2>Test an existing version</h2>
<form action="index.php?type=run" method="post">
<fieldset>

	<p>	
		<label for="testname">Choose a version</label>
		$selector
	</p>
	
	<p>	
		<label for="iterations">Number of iterations</label>
		<input type="text" name="iterations" id="iterations" maxlength="10" size="10" value="100" />
	</p>
	
	<p>	
		<label for="testcode">Test content (read-only)</label>
		<textarea name="testcode" id="testcode" cols="12" rows="6" readonly>
\$test_file = __DIR__.'/../test/MD_syntax.md';
require_once '../versions/ # VERSION # /markdown.php';
\$md_content = \$file_content = null;
\$file_content = file_get_contents(\$test_file);
\$md_content = Markdown( \$file_content );
		</textarea>
	</p>
	
</fieldset>

    <input type="hidden" name="version_test" value="1" />
	<input type="submit" value="Run this test" />
	<input type="reset" />
	
</form>
EOT;
			break;
			
		case 'testslist':
			$test_list = '';
			$header_tool .= getDbInput( isset($bdb) ? $bdb : null );
			$tests_presets = readPresets();
			if (null!==$_del = _get('delete', null)) {
				if ($ok = deletePreset( $_del ))
					$tests_presets = readPresets();
			}
			foreach($tests_presets as $testid=>$content) {
				$test = $content['name'];
//				$test_url = 'index.php?type=run&test='.urlencode($test);
				$test_url = 'index.php?type=run&test='.$testid;
				$duplicate_url = 'index.php?type=testcreate&preset='.$testid;
				$delete_url = 'index.php?type=testslist&delete='.$testid;
				$test_list .= '<li>'
					.'<strong><a href="#">'.$test.'</a></strong>'
					.'&nbsp;&nbsp;[&nbsp;for this preset:&nbsp;'
					.'<strong><a href="'.$test_url.'" title="Run a new instance of this test">run</a></strong>'
					.'&nbsp;|&nbsp;'
					.'<strong><a href="'.$duplicate_url.'" title="Define a new test from this preset values">duplicate</a></strong>'
					.'&nbsp;|&nbsp;'
					.'<strong><a href="'.$delete_url.'" title="Delete this preset">delete</a></strong>'
					.'&nbsp;]'
					.( !empty($content['description']) ? '<br />'.$content['description'] : '' )
					.'<br /><em>Test to tun on '.$content['iterations'].' iterations</em>'
					.'</li>';
			}
			$ctt .= <<<EOT
<h2>Test presets</h2>
<ul>
	{$test_list}
</ul>
EOT;
			break;
			
		case 'run':
			$test = _get('test');
			$header_tool .= getDbInput( isset($bdb) ? $bdb : null );
			$tests_set = readPresets();
			$tests_list = getVersionsList();
			$tested_version = _post('tested_version');
			if (isset($tests_set[$test])) {
				$ctt .= '<p>Launching test '.$test.': '.$tests_set[$test]['description'].' [ '.$iterations.' iterations ]</p>';
				$bench->newTest( $test, $tests_set[$test]['description'].' [ '.$iterations.' iterations ]' );
				$toeval = $tests_set[$test]['code'];

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
			} elseif (in_array($tested_version, $tests_list)) {
				$ctt .= '<p>OK - Classic test version done</p>';
			} else {
				$ctt .= '<p>Test '.$test.' not found!</p>';
			}
			break;

		case 'benchmark':
			if ($_del = _get('delete')) {
				$bench->deleteTestData( $_del );
			}
			if ($_test = _get('test')) {
				$data = $bench->readTestView_AllData( 'BENCHMARK', $_test );
				if (!empty($data)) $ctt .= "<p>All data</p>".$bench->viewBenchmarkData( $data, true );
			} else {
				$sumdata = $bench->readTestView_Summarize( 'BENCHMARK' );
				if (!empty($sumdata)) {
					$_ref = _get('ref');
					$ref = null!==$_ref ? $_ref : 'max';
					$header_tool .= getDbSelect( $bench, isset($bdb) ? $bdb : null );
					$block_global = buildShowHideBlock( 
						"<p>Change reference used for graphs: <a href=\"index.php?type=benchmark&ref=max\">max</a> | <a href=\"index.php?type=benchmark&ref=min\">min</a></p>"
						.$bench->viewBenchmark( $sumdata, $relations, $ref, 'index.php?type=benchmark&ref=%s' ), 
						"Global benchmark results"
					);
					$block_summarize = buildShowHideBlock( 
						$bench->viewBenchmarkData( $sumdata, true, 'index.php?type=benchmark&delete=%s' ),
						"Summarize", true
					);
					$ctt .= $block_global.$block_summarize;
				}
			}
			break;

		default:break;
	}
} else {

	$overview = $bench->viewBenchmarksList( 'table', 'index.php?type=benchmark&db=%s', 'filename' );
	$ctt .= <<<EOT
<h2>Benchmark tool</h2>
<p>Welcome on the <em>benchmark tool</em>. This application will help you to bench some PHP code and compare execution times to test optimization of the code. It allows you to build different <em>benchmarks</em> sets to separate your tests, and proposes a simple visualization of tests results with the ability to set a specific test as a reference.</p>
<p>The goal here is to estimate precisely some PHP parsing execution time by evaluating a code snippet (<em>a short peace of code doing a simple work</em>) many times and build an average value that will be considered pertinent as the "global execution time of the snippet".</p>
<h2>Begin a new benchmark</h2>
<p>To construct a new test case benchmark, you can:
<ul>
	<li>use the <a href="index.php?type=testcreate">web form</a> to execute your code directly by a web interface</li>
	<li>execute a <a href="index.php?type=testslist">predefined preset</a> to follow code evolution - your presets are built along your created tests</li>
</ul></p>
<h2>Benchmark archives</h2>
<p>The table below presents an overview of your archived tests. Clic on a line to visualize test results.</p>
	{$overview}
EOT;

}

// ------------------------------
// VIEW
// ------------------------------

// DEBUG
if (defined('_MODE') && _MODE==='debug')
{
	echo '<pre>';
	var_export($bench);
	exit('yo');
}

// CONTENTS
	$console='';
	if (!empty($console_ctt)){
		$console = '<div class="console">'
			.'<h3>TEST EXECUTION CONSOLE</h3>'
			.'<ul><li>'
			.implode('</li><li>', $console_ctt)
			.'</li></ul></div>';
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
h1           { color: #444; font-weight: bold; margin: 36px 10px; padding: 0; float: left;}
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
textarea { margin: 0; width:auto; min-width: 380px; height:auto; min-height: 120px; }

#wrapper     { margin: 0 1em; min-height: 100%; padding: 10px; position: relative; }
#footer      { margin: 6px 3em 20px 3em; padding: 4px; border-top: 1px solid #cccccc; font-size: .8em; }
.clear       { clear: both; }
.comment     { font-size: .9em; }
.header_tool { float: right; margin: 2em 2em 0 0;}
.header_tool p { font-size: .9em; font-weight: bold;}
a.show_hide_handler { font-size: .9em; font-weight: bold; margin-right: 20px; }
.required    { color: #003399; cursor: help; }
.console     { margin: 4px 2em; padding: 4px; border: 1px dotted #cccccc; background: #dddddd; }
.console h3  { color: #ffffff; font-weight: bold; font-size: .9em; margin-top: 0; }
.console li  { font-size: .9em; }
.error       { border: 1px solid red; }

.benchmark_graph             { float: left; display: inline-block; position: relative; margin: 2px; margin-left: 40px; width: 400px; height: 26px; border: 1px solid #ccc; background-color: #eee; }
.benchmark_ingraph           { height: 26px; display: inline-block; text-align: right; color: #fff; }
.benchmark_outgraph          { float: left; text-align: left; }
.benchmark_ingraph.colored_0 { background-color: #FFCC66; }
.benchmark_ingraph.colored_1 { background-color: #99CC66; }
.benchmark_ingraph.colored_2 { background-color: #3399CC; }
.benchmark_ingraph.colored_3 { background-color: #00FFFF; }
.benchmark_ingraph.colored_4 { background-color: #33FFFF; }
.benchmark_ingraph.colored_5 { background-color: #FFCC66; }
.benchmark_ingraph.colored_6 { background-color: #99CC66; }
.benchmark_ingraph.colored_7 { background-color: #3399CC; }
.benchmark_ingraph.colored_8 { background-color: #00FFFF; }
.benchmark_ingraph.colored_9 { background-color: #33FFFF; }
.benchmark_ingraph.colored_10 { background-color: #FFCC66; }
.benchmark_ingraph.colored_11 { background-color: #99CC66; }
.benchmark_ingraph.colored_12 { background-color: #3399CC; }
.benchmark_ingraph.colored_13 { background-color: #00FFFF; }
.benchmark_ingraph.colored_14 { background-color: #33FFFF; }

#navigation_menu {
	display: block; text-align:left; background: #eee; width:100%; height:20px; margin:0 auto 20px auto; padding: 4px; }
#navigation_menu ul          { padding:0; margin:0; list-style-type:none; }
#navigation_menu ul li       { padding:0; margin:0 10px; float:left; position:relative; list-style-type:none; width: auto; line-height:20px; }
#navigation_menu ul li ul    { padding:0; margin:0; visibility:hidden; position:absolute; top:0; left:0; height:0; overflow:hidden; }
#navigation_menu ul li ul li { padding:4px; margin:0; list-style-type: none; line-height:20px; height: 20px; width: auto; min-width: 100px; }
#navigation_menu a, #navigation_menu a:visited {
	display:inline-block; text-decoration:none; padding:0 4px; margin:0; }
#navigation_menu li li a, #navigation_menu li li a:visited { font-size 0.96em; line-height: 20px; }
#navigation_menu li.first { font-weight: bold; width: auto !important; }
#navigation_menu ul li:hover { background-color: #404040; }
#navigation_menu a:hover { color:#fff; text-decoration:none; border:0; }
#navigation_menu li.submenu:after { content: ' >>';}
#navigation_menu ul li:hover ul, #navigation_menu ul li a:hover ul {
	visibility:visible; width:auto; height:auto; position:absolute; top:24px; left:-4px; 
	background:#eee; border:1px solid #fff; overflow:visible; z-index:10; }
	</style>
	<script type="text/javascript"><!--//
function seeOtherBenchnmark( _name ){
	document.location.href='index.php?type=benchmark&db='+_name;
}
function show_hide( id ){
	var domobj = document.getElementById( id );
	if (domobj){
		var _visible = domobj.style.visibility;
		if (_visible=='hidden'){
			domobj.style.visibility='visible';
			domobj.style.display='block';
			window.location.hash = id+'_hd';
		} else {
			domobj.style.visibility='hidden';
			domobj.style.display='none';
			if (window.location.hash == '#'+id+'_hd')
				window.location.hash='#';
		}
	}
}
	//--></script>
</head>
<body>
	<div id="wrapper">
	<h1>Benchmark of Markdown execution</h1>
	<div class="header_tool">
		{$header_tool}
	</div>
	<br class="clear" />
	<div id="navigation_menu">
		<ul>
			<li class="first"><strong>MENU</strong></li>
			<li><a href="index.php" title="Homepage">Home</a></li>
			<li><a href="index.php?type=testcreate" title="Create a test by web interface">Create a test</a></li>
			<li><a href="index.php?type=testsversions" title="Choose an existing version to test">Tests versions</a></li>
			<li><a href="index.php?type=testslist" title="Choose a test to run">Tests presets</a></li>
			<li><a href="index.php?type=benchmark" title="See benchmark results">Analyze benchmark data</a></li>
		</ul>
	</div>
	{$console}
	{$ctt}
	</div>
EOT;
$_exec_end_time = microtime();
$footer = date('D').' <strong>'.date('d M Y H:i:s').'</strong> '.date_default_timezone_get().' [<abbr title="ISO date"><em>'.date('c').'</em></abbr>]'
	.'&nbsp;&nbsp;|&nbsp;&nbsp;Page generated in <strong>'._execution_time($_exec_start_time, $_exec_end_time).' seconds</strong>'
	.'&nbsp;&nbsp;|&nbsp;&nbsp;PHP vers. <strong>'.phpversion().'</strong> ('.php_sapi_name().')'
	.'&nbsp;&nbsp;|&nbsp;&nbsp;SQLite vers. <strong>'.sqlite_libversion().'</strong>'
	.'<br />Webserver system :&nbsp;&nbsp;<strong>'.apache_get_version().'</strong>'
	.'<br />Server OS :&nbsp;&nbsp;<strong>'.php_uname().'</strong>'
	.'<br />Browser / Device :&nbsp;&nbsp;<strong>'.$_SERVER['HTTP_USER_AGENT'].'</strong>'
	;
echo <<<EOT
<div id="footer">{$footer}</div>
</body>
</html>
EOT;
	exit;

// Endfile