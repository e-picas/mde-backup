<?php
# file generated at 17-11-2012 10:58:53
$tests_presets=array (
  0 => 
  array (
    'name' => 'Markdown',
    'description' => '"Markdown" initial PHP version, test on file \'$test_file\' (getting content and parsing for each iteration).',
    'iterations' => 100,
    'code' => '$test_file = __DIR__.\'/../test/MD_syntax.md\';
require_once \'../versions/PHP_Markdown_1.0.1o/markdown.php\';
$md_content = $file_content = null;
$file_content = file_get_contents($test_file);
$md_content = Markdown( $file_content );',
  ),
  1 => 
  array (
    'name' => 'Markdown_Extra',
    'description' => '"Markdown_Extra" extended version, test on file \'$test_file\' (getting content and parsing for each iteration).',
    'iterations' => 100,
    'code' => '$test_file = __DIR__.\'/../test/MD_syntax.md\';
require_once \'../versions/PHP_Markdown_Extra_1.2.5/markdown.php\';
$md_content = $file_content = null;
$file_content = file_get_contents($test_file);
$md_content = Markdown( $file_content );',
  ),
  2 => 
  array (
    'name' => 'Markdown_Extended_original',
    'description' => 'Original "Markdown_Extended" version, test on file \'$test_file\' (getting content and parsing for each iteration).',
    'iterations' => 100,
    'code' => '$test_file = __DIR__.\'/../test/MD_syntax.md\';
require_once \'../versions/PHP_Extended_Markdown/markdown.php\';
$md_content = $file_content = null;
$file_content = file_get_contents($test_file);
$md_content = Markdown( $file_content );',
  ),
  3 => 
  array (
    'name' => 'emd2html',
    'description' => 'Simple quoted "Markdown_Extended" in new compiled version with no \'call_user_func()\' calls, test on file \'$test_file\' (getting content and parsing for each iteration).',
    'iterations' => 100,
    'code' => '$test_file = __DIR__.\'/../test/MD_syntax.md\';
require_once \'../build/markdown_dev.php\';
$md_content = $file_content = null;
$file_content = file_get_contents($test_file);
$md_content = Markdown2Html( $file_content );',
  ),
  4 => 
  array (
    'name' => 'this_Markdown_Extended_direct_notstatic',
    'description' => 'Simple quoted "Markdown_Extended" in new compiled version with no \'call_user_func()\' calls, test on file \'$test_file\' (getting content and parsing for each iteration).',
    'iterations' => 100,
    'code' => '$test_file = __DIR__.\'/../test/MD_syntax.md\';
require_once \'../versions/PHP_Extended_Markdown_notstatic_outputFormat/markdown.php\';
$md_content = $file_content = null;
$file_content = file_get_contents($test_file);
$md_content = Markdown( $file_content );',
  ),
  5 => 
  array (
    'name' => 'emd2html_notstatic',
    'description' => '',
    'iterations' => 100,
    'code' => '$test_file = __DIR__.\'/../test/MD_syntax.md\';
require_once \'../versions/emd2html_notstatic/markdown.php\';
$md_content = $file_content = null;
$file_content = file_get_contents($test_file);
$md_content = Markdown( $file_content );',
  ),
  6 => 
  array (
    'name' => 'emd2html_noOutputFormatMethods',
    'description' => '',
    'iterations' => 100,
    'code' => '$test_file = __DIR__.\'/../test/MD_syntax.md\';
require_once \'../versions/emd2html_notOutputFormatMethods/markdown.php\';
$md_content = $file_content = null;
$file_content = file_get_contents($test_file);
$md_content = Markdown( $file_content );',
  ),
  7 => 
  array (
    'name' => 'this_Markdown_Extended',
    'description' => 'Simple quoted "Markdown_Extended" in new compiled version, test on file \'$test_file\' (getting content and parsing for each iteration).',
    'iterations' => 100,
    'code' => '$test_file = __DIR__.\'/../test/MD_syntax.md\';
require_once \'../src/markdown.php\';
$md_content = $file_content = null;
$file_content = file_get_contents($test_file);
$md_content = Markdown( $file_content );',
  ),
  8 => 
  array (
    'name' => 'singleton_Markdown_Extended',
    'description' => '"Markdown_Extended" in new compiled version using singelton instance, test on file \'$test_file\' (getting content and parsing for each iteration).',
    'iterations' => 100,
    'code' => '$test_file = __DIR__.\'/../test/MD_syntax.md\';
require_once \'../src/markdown.php\';
$md_content = $file_content = null;
$file_content = file_get_contents($test_file);
$md_content = MarkdownAsSingleton( $file_content );',
  ),
  9 => 
  array (
    'name' => 'OO_Markdown_Extended',
    'description' => '"Markdown_Extended" object-oriented, test on file \'$test_file\' (getting content and parsing for each iteration).',
    'iterations' => 100,
    'code' => '$test_file = __DIR__.\'/../test/MD_syntax.md\';
require_once \'../versions/OO_Extended_Markdown/markdown.php\';
$md_content = $file_content = null;
$file_content = file_get_contents($test_file);
$md_content = Markdown( $file_content );',
  ),
  13 => 
  array (
    'name' => 'singleton_Markdown_Extended',
    'iterations' => '100',
    'description' => '"Markdown_Extended" in new compiled version using singelton instance, test on file \'$test_file\' (getting content and parsing for each iteration).',
    'code' => '$test_file = __DIR__.\'/../test/MD_syntax.md\';
require_once \'../src/markdown.php\';
$md_content = $file_content = null;
$file_content = file_get_contents($test_file);
$md_content = MarkdownAsSingleton( $file_content );',
  ),
  14 => 
  array (
    'name' => 'Markdown_Extended_original',
    'iterations' => '100',
    'description' => 'Original "Markdown_Extended" version, test on file \'$test_file\' (getting content and parsing for each iteration).',
    'code' => '$test_file = __DIR__.\'/../test/MD_syntax.md\';
require_once \'../versions/PHP_Extended_Markdown/markdown.php\';
$md_content = $file_content = null;
$file_content = file_get_contents($test_file);
$md_content = Markdown( $file_content );',
  ),
  15 => 
  array (
    'name' => 'singleton_Markdown_Extended',
    'iterations' => '100',
    'description' => '"Markdown_Extended" in new compiled version using singelton instance, test on file \'$test_file\' (getting content and parsing for each iteration).',
    'code' => '$test_file = __DIR__.\'/../test/MD_syntax.md\';
require_once \'../src/markdown.php\';
$md_content = $file_content = null;
$file_content = file_get_contents($test_file);
$md_content = MarkdownAsSingleton( $file_content );',
  ),
  16 => 
  array (
    'name' => 'singleton_Markdown_Extended',
    'iterations' => '100',
    'description' => '\\"Markdown_Extended\\" in new compiled version using singelton instance, test on file \\\'$test_file\\\' (getting content and parsing for each iteration).',
    'code' => '$test_file = __DIR__.\'/../test/MD_syntax.md\';
require_once \'../src/markdown.php\';
$md_content = $file_content = null;
$file_content = file_get_contents($test_file);
$md_content = MarkdownAsSingleton( $file_content );',
  ),
  17 => 
  array (
    'name' => 'emd2html_noOutputFormatMethods_noOptions',
    'iterations' => '100',
    'description' => 'emd2html output format with no Options stack in the full object : all option is set as an object property.',
    'code' => '$test_file = __DIR__.\'/../test/MD_syntax.md\';
require_once \'../versions/emd2html_notOutputFormatMethods_noOptions/markdown.php\';
$md_content = $file_content = null;
$file_content = file_get_contents($test_file);
$md_content = Markdown( $file_content );',
  ),
);
?>