<?php
/**
 * Work in progress ....
 *
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
// PROCESS
// ------------------------------

$md_content=$alt_content='';

if (!empty($_GET) && isset($_GET['type'])) {
	$test_file = __DIR__.'/../test/MD_syntax.md';
	$file_content = file_get_contents( $test_file );
	switch($_GET['type']) 
	{
		case 'testoptions':
			require __DIR__.'/markdown_dev.php';
			$parser = new PHP_Extended_Markdown( 'generated/emd_config.ini' );
			$md_content = $parser->doDebug( '', $parser->getOption() );
			break;
		case 'processmarkdown':
			require __DIR__.'/markdown_dev.php';
			$alt_content = '<p>'.PHP_Extended_Markdown::info(true).'</p><hr />';
			if (empty($md_content)) $md_content = Markdown( $file_content, 'generated/emd_config.ini' );
			break;
		case 'rebuildmarkdown':
			define('MARKDOWN_MODE', 'REBUILD');
			require __DIR__.'/markdown_dev.php';
			$alt_content = '<p>'.PHP_Extended_Markdown::info(true).'</p><hr />';
			if (empty($md_content)) $md_content = Markdown( $file_content, 'generated/emd_config.ini' );
			break;
		case 'debugmarkdown':
			require __DIR__.'/markdown_dev.php';
			$alt_content = '<p>'.PHP_Extended_Markdown::info(true).'</p><hr />';
			if (empty($md_content)) $md_content = MarkdownDebug( $file_content, 'generated/emd_config.ini', 'hard' );
			break;
		default: case 'plain':
			$alt_content = '<p>Original Markdown test file content : \'MD_syntax.md\'.</p>';
			$md_content = '<pre class="long">'.$file_content.'</pre>';
			break;
	}
} else {
	$md_content = '<div style="margin: 2em 10em;">'
		.'<p><strong>Choose an entry in the above menu ...</strong></p>'
		.'<p>To make a test of the command line interface, run:</p>'
		.'<pre>
~$ cd path/to/markdown.php
~$ php emd_console -x -o test/MD_Syntax_test.html test/MD_syntax.md
</pre>'
		.'<p>The result of the parsing must be in file "test/MD_Syntax_test.html".</p>'
		.'</div>';
}

// ------------------------------
// VIEW
// ------------------------------

	echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Test page of the PHP Extended Markdown parser</title>
<style type="text/css">
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

pre.long     { max-height:500000px; }
#wrapper     { margin: 0 1em; min-height: 100%; padding: 10px; position: relative; }
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
	<div style="float:right;text-align:right">
		<p style="font-weight: bold;">Test page of the PHP Extended Markdown parser</p>
		<p style="font-size: .8em">For more infos, see : <a href="https://github.com/PieroWbmstr/Extended_Markdown">https://github.com/PieroWbmstr/Extended_Markdown</a></p>
	</div>
	<div style="float:left">
		<h2 style="float: left;">MENU</h2>
		<ul style="float: left;margin: auto 12px;">
			<li><a href="index_dev.php?type=plain">See the original Markdown test file content</a></li>
			<li><a href="index_dev.php?type=processmarkdown">See the Markdown test file processed with PHP Extended Markdown</a></li>
			<li><a href="index_dev.php?type=rebuildmarkdown">See the Markdown test file processed with PHP Extended Markdown with Grammar and configuration rebuild each time</a></li>
			<li><a href="index_dev.php?type=debugmarkdown">See the Markdown test file processed with PHP Extended Markdown in DEBUG mode</a></li>
			<li><a href="index_dev.php?type=testoptions">See instance options</a></li>
		</ul>
	</div>
	<hr style="clear: both;" />
	{$alt_content}
	{$md_content}
</div>
</body>
</html>
EOT;
	exit;

// Endfile