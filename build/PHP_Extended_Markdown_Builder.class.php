<?php
/**
 * PHP Extended Markdown
 * Copyright (c) 2012 Pierre Cassat
 *
 * original MultiMarkdown
 * Copyright (c) 2005-2009 Fletcher T. Penney
 * <http://fletcherpenney.net/>
 *
 * original PHP Markdown & Extra
 * Copyright (c) 2004-2012 Michel Fortin  
 * <http://michelf.com/projects/php-markdown/>
 *
 * original Markdown
 * Copyright (c) 2004-2006 John Gruber  
 * <http://daringfireball.net/projects/markdown/>
 *
 * @package 	PHP_Extended_Markdown
 * @subpackage 	PHP_Extended_Markdown_DevelopmentTools
 * @license   	BSD
 * @link      	https://github.com/PieroWbmstr/Extended_Markdown
 */

// show errors at least initially
ini_set('display_errors','1'); error_reporting(E_ALL ^ E_NOTICE);

class PHP_Extended_Markdown_Builder
{

	public $user_manual_filename				= '';
	public $developer_manual_filename 			= '';
	public $grammar_filename					= '';
	public $outputformat_interface_filename		= '';
	public $config_filename						= '';
	public $directory_to_scan					= 'Grammar';
	public $templates_directory 				= 'templates';
	public $compiler_directory 					= 'generated';
	public $allowed_extensions					= array( 'php' );
	public $force_rebuild						= false;

	protected $contents = array();
	protected static $emd_builder_tag = '##@emd@##';
	protected static $builds = array(
		'config'=>array(
			'tag'=>'CONFIG',
			'template'=>'emd_config.tpl.ini',
			'filename'=>'emd_config.ini',
			'type'=>'ini',
		),
		'grammar'=>array(
			'tag'=>'GRAMMAR',
			'template'=>'PHP_Extended_Markdown_Grammar.tpl.php',
			'filename'=>'PHP_Extended_Markdown_Grammar.class.php',
			'type'=>'php',
		),
		'outputformat_interface'=>array(
			'tag'=>'OUTPUTFORMAT_INTERFACE',
			'template'=>'PHP_Extended_Markdown_OutputFormat.tpl.php',
			'filename'=>'PHP_Extended_Markdown_OutputFormat.class.php',
			'type'=>'php',
		),
		'user_manual'=>array(
			'tag'=>'USER_MANUAL',
			'template'=>'USER_MANUAL.tpl.md',
			'filename'=>'USER_MANUAL.md',
			'type'=>'txt',
		),
		'developer_manual'=>array(
			'tag'=>'DEV_MANUAL',
			'template'=>'DEV_MANUAL.tpl.md',
			'filename'=>'DEVELOPER_MANUAL.md',
			'type'=>'txt',
		),
	);

	protected static function getTag( $name, $type='opener' )
	{
		if (!isset(self::$builds[$name]) || !isset(self::$builds[$name]['tag']))
		{
			throw new Exception( sprintf("Calling an unknown tag '%s'!", $name) );
		}
		return self::$emd_builder_tag.' '
			.( 'closer'===$type ? '!' : '' )
			.self::$builds[$name]['tag']
			.' '.self::$emd_builder_tag;
	}

	protected static function getTemplateTag( $name )
	{
		return '{%'.$name.'%}';
	}

	public function writeFile( $filename, $content ) 
	{
		// is the filename set ?
		if (!empty($filename) && !empty($content))
		{
			// does filename exists and the compiler is not forced ?
			if (@file_exists($filename))
			{
				if (false===$this->force_rebuild)
				{
					throw new Exception( sprintf("File to build by Markdown dev compiler already exists ('%s')! Use 'force' option to overwrite it.", $filename) );
				}
				else
				{
					if (false===unlink($filename))
					{
						throw new Exception( sprintf("File to build by Markdown dev compiler already exists and can't be deleted ('%s')!", $filename) );
					}
				}
			}
		}
		// ok ...
		if (false===$ok = file_put_contents( $filename, $content))
		{
			throw new Exception( sprintf("Can't write compiled file ('%s')!", $filename) );
		}
		return $ok;
	}
	
	public function compile( $force_rebuild=false ) 
	{
		$this->force_rebuild = $force_rebuild;

		// are the directories correctly set ?
		$dirs = array(
			'directory_to_scan'=>$this->directory_to_scan,
			'templates_directory'=>$this->templates_directory,
			'compiler_directory'=>$this->compiler_directory,
		);
		foreach($dirs as $_var=>$_dir)
		{
			// does it exist ?
			if (!@file_exists($_dir) || !@is_dir($_dir))
			{
				throw new Exception( 
					sprintf("Directory named '%s' of dev compiler doesn't exist ('%s')!", $_var, $_dir) 
				);
			}
		}

		// then let's go...
		return $this->_build();
	}

	protected function _build() 
	{
		$_php = $this->_scandir( $this->directory_to_scan );
		foreach(self::$builds as $_name=>$_stack)
		{
			$_fn = $_name.'_filename';
			$_compiled_ctt = $this->_buildContent( $_name );
			$filename = (property_exists($this, $_fn) && !empty($this->$_fn)) ?
				$this->$_fn : $_stack['filename'];
			$dirname = rtrim($this->compiler_directory, '/').'/';
			if (false===$ok = file_put_contents( $dirname.$filename, $_compiled_ctt))
			{
				throw new Exception( sprintf("Can't write compiled file ('%s')!", $dirname.$filename) );
			}
		}		
		return $ok;
	}
	
	protected function _scandir( $dir ) 
	{
		if (!@file_exists($dir))
		{
			throw new Exception( sprintf("Directory '%s' does not exist!", $dir) );
		}
		if (!@is_dir($dir))
		{
			throw new Exception( sprintf("Directory '%s' is not a directory!", $dir) );
		}

		$d = scandir($dir);
		if (false!==$d)
		{
			foreach ($d as $f) 
			{
				if (!in_array($f, array('.', '..'))){
					$f_path = $dir.'/'.$f;
					if (
						is_file($f_path) && 
						in_array(end(explode('.', $f_path)), $this->allowed_extensions)
					){
						$this->_parseAllTags( $f_path );
					} elseif (is_dir($f_path)) {
						$this->_scandir( $f_path );
					}
				}
			}		
		}
	}
	
	protected function _buildTitle( $file_path, $format='php' ) 
	{
		$file_name = strtoupper( str_replace('_', ' ', reset( explode('.', end( explode('/', $file_path) ) ) ) ) );
		if ('php'===$format)
		{
			return <<<EOT

// -----------------------------------
// {$file_name}
// -----------------------------------

EOT;
		}
		elseif ('ini'===$format)
		{
			return <<<EOT

; {$file_name}

EOT;
		}
		elseif ('txt'===$format)
		{
			return <<<EOT

## {$file_name}

EOT;
		}
	}
	
	protected function _parseAllTags( $file_path ) 
	{
		$file_ctt = file( $file_path, FILE_IGNORE_NEW_LINES );
		foreach(self::$builds as $_name=>$_stack)
		{
			$_ctt_str = $this->_extract( $file_ctt, $_name );
			if (strlen($_ctt_str))
			{
				if (!isset($this->contents[ $_name ]))
				{
					$this->contents[ $_name ] = ''; 
				}
				$this->contents[ $_name ] .= 
					$this->_buildTitle( $file_path, $_stack['type']) . $_ctt_str;
			}
		}
	}

	protected function _extract( $lines, $tag_name ) 
	{
		$tag_opener = self::getTag( $tag_name, 'opener' );
		$tag_closer = self::getTag( $tag_name, 'closer' );
		$table=array();
		$in_tag = false;
		foreach($lines as $_line)
		{
			if (false===$in_tag)
			{
				if ($tag_opener==trim($_line))
				{
					$in_tag=true;
				}
			}
			else
			{
				if ($tag_closer==trim($_line))
				{
					$in_tag=false;
					continue;
				}
				else
				{
					$table[] = $_line;
				}
			}
		}
		return join("\n", $table);
	}

	protected function getTemplateFile( $filename )
	{
		$tpl_f = $this->templates_directory.'/'.$filename;
		if (!@file_exists($tpl_f))
		{
			throw new Exception( sprintf("Template file to build compiler content can't be found (%s)!", $filename) );
		}
		return $tpl_f;
	}
	
	protected function parseTemplateContent( $template, $args=array() )
	{
		$args['date'] = date('Y-m-d H:i:s');
		foreach($args as $name=>$value)
		{
			$template = str_replace(
				$this->getTemplateTag( strtoupper($name) ), $value, $template
			);
		}
		return $template;
	}
	
	protected function _buildContent( $type )
	{
		if (!isset(self::$builds[ $type ]))
		{
			throw new Exception( sprintf("Unknown builder type '%s'!", $type) );
		}
		$entry = self::$builds[ $type ];
		$content = $this->contents[ $type ];
		$tpl_f = $this->getTemplateFile( $entry['template'] );
		$template = file_get_contents($tpl_f);
		return $this->parseTemplateContent(
			$template, array( $entry['tag']=>$content )
		);
	}

}

// Endfile