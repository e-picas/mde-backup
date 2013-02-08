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

// -----------------------------------
// OVER-WRITE DEFAULT SETTINGS
// -----------------------------------

// Change to ">" for HTML output
define( 'MARKDOWN_EMPTY_ELEMENT_SUFFIX',  " />");

// Define the width of a tab for code blocks.
define( 'MARKDOWN_TAB_WIDTH',             4 );

// Regex to match balanced [brackets].
define( 'MARKDOWN_NESTED_BRACKETS_DEPTH', 6 );

// Regex to match balanced (parenthesis).
define( 'MARKDOWN_NESTED_URL_PARENTHESIS_DEPTH', 4 );

// Table of hash values for escaped characters:
define( 'MARKDOWN_ESCAPE_CHARS',          '\`*_{}[]()>#+-.!:|' );

// Optional title attribute for footnote links and backlinks.
define( 'MARKDOWN_FN_LINK_TITLE',         "See footnote %%" );
define( 'MARKDOWN_FN_BACKLINK_TITLE',     "Return to content" );

// Optional class attribute for footnote links and backlinks.
define( 'MARKDOWN_FN_LINK_CLASS',         "footnote" );
define( 'MARKDOWN_FN_BACKLINK_CLASS',     "reverse_footnote" );

// Optional id attribute prefix for footnote links and backlinks.
define( 'MARKDOWN_FN_ID_PREFIX',          "" );

// Optional title attribute for glossary footnote links and backlinks.
define( 'MARKDOWN_FNG_LINK_TITLE',        "See glossary entry %%" );
define( 'MARKDOWN_FNG_BACKLINK_TITLE',    "Return to content" );

// Optional class attribute for glossary footnote links and backlinks.
define( 'MARKDOWN_FNG_LINK_CLASS',        "footnote_glossary" );
define( 'MARKDOWN_FNG_BACKLINK_CLASS',    "reverse_footnote_glossary" );

// Optional id attribute prefix for glossary footnote links and backlinks.
define( 'MARKDOWN_FNG_ID_PREFIX',         "" );

// Optional title attribute for citation footnote links and backlinks.
define( 'MARKDOWN_FNC_LINK_TITLE',        "See bibliography reference %%" );
define( 'MARKDOWN_FNC_BACKLINK_TITLE',    "Return to content" );

// Optional class attribute for citation footnote links and backlinks.
define( 'MARKDOWN_FNC_LINK_CLASS',        "footnote_bibliography" );
define( 'MARKDOWN_FNC_BACKLINK_CLASS',    "reverse_footnote_bibliography" );

// Optional id attribute prefix for citation footnote links and backlinks.
define( 'MARKDOWN_FNC_ID_PREFIX',         "" );

// The mask used for MetaData named Title
define( 'MARKDOWN_METADATA_MASK_TITLE',   '<%1$s>%2$s</%1$s>' );

// The default mask used for MetaData
define( 'MARKDOWN_METADATA_MASK',         '<meta name="%s" content="%s" />' );

// Tags that are always treated as block tags
define( 'MARKDOWN_BLOCKS_TAGS_RE',        'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|form|fieldset|iframe|hr|legend' );

// Tags treated as block tags only if the opening tag is alone on it's line
define( 'MARKDOWN_CONTEXT_BLOCKS_TAGS_RE','script|noscript|math|ins|del' );

// Tags where markdown="1" default to span mode
define( 'MARKDOWN_CONTAIN_SPAN_TAGS_RE',  'p|h[1-6]|li|dd|dt|td|th|legend|address' );

// Tags which must not have their contents modified, no matter where they appear
define( 'MARKDOWN_CLEAN_TAGS_RE',         'script|math' );

// Tags that do not need to be closed
define( 'MARKDOWN_CLOSE_TAGS_RE',         'hr|img' );

// ------------------------------
// PROCESS
// ------------------------------

$test_file = __DIR__.'/test/MD_syntax.md';

require __DIR__.'/PHP_Extended_Markdown_API.class.php';

$options = array();
//$parser = PHP_Extended_Markdown::getInstance( $options );

$parser = new PHP_Extended_Markdown_API( $options );
$parser->load( $test_file );
$parser->parse();

$_md = $parser->getContent();
//$parser->doDebug();

echo '<pre>';

var_export($parser->getParser()->footnotes);
var_export($parser->getParser()->glossaries);
var_export($parser->getParser()->citations);
var_export($parser->getParser()->urls);
var_export($parser->getParser()->titles);
var_export($parser->getParser()->attributes);
var_export($parser->getParser()->ids);

var_export($_md);

var_export($parser);

exit('end');
// Endfile