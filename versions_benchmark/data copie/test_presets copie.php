<?php
$tests_presets=array(
	array(
		'name'=>'Markdown',
		'description'=>"\"Markdown\" initial PHP version, test on file '\$test_file' (getting content and parsing for each iteration).",
		'iterations'=>100,
		'code'=>"\$test_file = __DIR__.'/../../test/MD_syntax.md';
require_once '../PHP_Markdown_1.0.1o/markdown.php';
\$md_content = \$file_content = null;
\$file_content = file_get_contents('\$test_file');
\$md_content = Markdown( \$file_content );",
	),
	array(
		'name'=>'Markdown_Extra',
		'description'=>"\"Markdown_Extra\" extended version, test on file '\$test_file' (getting content and parsing for each iteration).",
		'iterations'=>100,
		'code'=>"\$test_file = __DIR__.'/../../test/MD_syntax.md';
require_once '../PHP_Markdown_Extra_1.2.5/markdown.php';
\$md_content = \$file_content = null;
\$file_content = file_get_contents('\$test_file');
\$md_content = Markdown( \$file_content );",
	),
	array(
		'name'=>'Markdown_Extended_original',
		'description'=>"Original \"Markdown_Extended\" version, test on file '\$test_file' (getting content and parsing for each iteration).",
		'iterations'=>100,
		'code'=>"\$test_file = __DIR__.'/../../test/MD_syntax.md';
require_once '../PHP_Extended_Markdown/markdown.php';
\$md_content = \$file_content = null;
\$file_content = file_get_contents('\$test_file');
\$md_content = Markdown( \$file_content );",
	),
	array(
		'name'=>'emd2html',
		'description'=>"Simple quoted \"Markdown_Extended\" in new compiled version with no 'call_user_func()' calls, test on file '\$test_file' (getting content and parsing for each iteration).",
		'iterations'=>100,
		'code'=>"\$test_file = __DIR__.'/../../test/MD_syntax.md';
require_once '../../build/markdown_dev.php';
\$md_content = \$file_content = null;
\$file_content = file_get_contents('\$test_file');
\$md_content = Markdown2Html( \$file_content );",
	),
	array(
		'name'=>'this_Markdown_Extended_direct_notstatic',
		'description'=>"Simple quoted \"Markdown_Extended\" in new compiled version with no 'call_user_func()' calls, test on file '\$test_file' (getting content and parsing for each iteration).",
		'iterations'=>100,
		'code'=>"\$test_file = __DIR__.'/../../test/MD_syntax.md';
require_once '../PHP_Extended_Markdown_notstatic_outputFormat/markdown.php';
\$md_content = \$file_content = null;
\$file_content = file_get_contents('\$test_file');
\$md_content = Markdown( \$file_content );",
	),
	array(
		'name'=>'emd2html_notstatic',
		'description'=>"",
		'iterations'=>100,
		'code'=>"\$test_file = __DIR__.'/../../test/MD_syntax.md';
require_once '../emd2html_notstatic/markdown.php';
\$md_content = \$file_content = null;
\$file_content = file_get_contents('\$test_file');
\$md_content = Markdown( \$file_content );",
	),
	array(
		'name'=>'emd2html_noOutputFormatMethods',
		'description'=>"",
		'iterations'=>100,
		'code'=>"\$test_file = __DIR__.'/../../test/MD_syntax.md';
require_once '../emd2html_notOutputFormatMethods/markdown.php';
\$md_content = \$file_content = null;
\$file_content = file_get_contents('\$test_file');
\$md_content = Markdown( \$file_content );",
	),
	array(
		'name'=>'this_Markdown_Extended',
		'description'=>"Simple quoted \"Markdown_Extended\" in new compiled version, test on file '\$test_file' (getting content and parsing for each iteration).",
		'iterations'=>100,
		'code'=>"\$test_file = __DIR__.'/../../test/MD_syntax.md';
require_once '../../markdown.php';
\$md_content = \$file_content = null;
\$file_content = file_get_contents('\$test_file');
\$md_content = Markdown( \$file_content );",
	),
	array(
		'name'=>'singleton_Markdown_Extended',
		'description'=>"\"Markdown_Extended\" in new compiled version using singelton instance, test on file '\$test_file' (getting content and parsing for each iteration).",
		'iterations'=>100,
		'code'=>"\$test_file = __DIR__.'/../../test/MD_syntax.md';
require_once '../../markdown.php';
\$md_content = \$file_content = null;
\$file_content = file_get_contents('\$test_file');
\$md_content = MarkdownAsSingleton( \$file_content );",
	),
	array(
		'name'=>'OO_Markdown_Extended',
		'description'=>"\"Markdown_Extended\" object-oriented, test on file '\$test_file' (getting content and parsing for each iteration).",
		'iterations'=>100,
		'code'=>"\$test_file = __DIR__.'/../../test/MD_syntax.md';
require_once '../OO_Extended_Markdown/markdown.php';
\$md_content = \$file_content = null;
\$file_content = file_get_contents('\$test_file');
\$md_content = Markdown( \$file_content );",
	),
);
?>