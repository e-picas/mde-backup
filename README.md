PHP Extended Markdown
====================

An extended version of PHP Markdown parser as a Composer package to use in other projects


## Version Zero

This first version is just a portage form [Michel Fortin's MarkdownExtra](http://michelf.com/projects/php-markdown/)
to allow to use Markdwn as a [Composer](http://getcomposer.org/) package.

>   Markdown Extra  -  A text-to-HTML conversion tool for web writers
>   PHP Markdown & Extra
>   Copyright (c) 2004-2009 Michel Fortin  
>   <http://michelf.com/projects/php-markdown/>

## Usage

To use the classic Markdown parser:

>   $parser = new Markdown\Parser;
>   $md_content = $parser->transform( $md_content );

To use the MarkdownExtra parser:

>   $parser = new Markdown\ExtraParser;
>   $md_content = $parser->transform( $md_content );
