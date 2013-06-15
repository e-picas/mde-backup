PHP Markdown Extended
=====================

A complete PHP 5.3 package of Markdown syntax parser (extended version).

**PHP Markdown Extended** is a PHP class to transform Markdown text files or strings in
HTML. This new version of a Markdown parser tries to propose a complete set of Markdown
syntax tags and rules and to be PHP-5.3 compliant.


**WARNINGS** - This package is still in development and not yet proposed in a "stable" version ;
some works remains before version 1.0. To get informed about the first stable version, you
can "Watch" the development by clicking the "Watch" button on the GitHub repository homepage
at <http://github.com/atelierspierrot/markdown-extended>.


## What is Markdown?

Created by [John Gruber](http://daringfireball.net/projects/markdown/) in 2004, 
**Markdown** is, as he says:

>    a text-to-HTML conversion tool for web writers. Markdown allows you 
>    to write using an easy-to-read, easy-to-write plain text format, then convert it 
>    to structurally valid XHTML (or HTML).

As I would say, **Markdown** is a set of writing rules to build some human readable text 
contents, such as `.txt` common files, which can be parsed to build some HTML valid content,
structurally and typographically.

This syntax has become one of the most common standards of rich-text contents, used for
instance by [GitHub](http://github.com) as one of the proposed syntaxes for informational
files (such like this one).


### A short history of Markdown

The first idea was from [John Gruber](http://daringfireball.net/), coded in *Perl* script.

An extended implementation, known as **Markdown Extra**, has been written by [Michel Fortin](http://michelf.com/),
coded in *PHP* script.

Another extended implementation, known as **Multi Markdown**, has been written by 
[Fletcher T. Penney](http://fletcherpenney.net/), coded in *Perl* script.


### So why a new version of Markdown?

-   This version, known as **PHP Markdown Extended**, tries to collect the different rules
    and tags used by the three versions listed above.
-   It is a PHP script that can be used in any PHP project.
-   It is coded following the PHP 5.3 coding standards.
-   It can be used in command line interface, with a full set of options.
-   It can be configured for specific needs.


## How-to

As for all our work, we try to follow the coding standards and naming rules most commonly in use:

-   the [PEAR coding standards](http://pear.php.net/manual/en/standards.php)
-   the [PHP Framework Interoperability Group standards](https://github.com/php-fig/fig-standards).

Knowing that, all classes are named and organized in an architecture to allow the use of 
the [standard SplClassLoader](https://gist.github.com/jwage/221634).

The whole package is embedded in the `MarkdownExtended` namespace.


### Installation

You can use this package in your work in many ways.

First, you can clone the [GitHub](https://github.com/atelierspierrot/markdown-extended)
repository and include it "as is" in your poject:

    wget --no-check-certificate https://github.com/atelierspierrot/markdown-extended

You can also download an [archive](https://github.com/atelierspierrot/markdown-extended/downloads)
from Github.

Then, to use the package classes, you just need to register the `MarkdownExtended`
namespace directory using the [SplClassLoader](https://gist.github.com/jwage/221634) or
any other custom autoloader (if required, a copy is proposed in the package):

    require_once 'path/to/package/src/SplClassLoader.php';
    $classLoader = new SplClassLoader('MarkdownExtended', '/path/to/package/src');
    $classLoader->register();

Another way to use the package, if you are a [Composer](http://getcomposer.org/) user,
is to add it to your requirements in your `composer.json`:

    "require": {
        ...
        "atelierspierrot/markdown-extended": "dev-master"
    }

The namespace will be automatically added to the project's Composer autoloader.


### Usage

#### PHP usage

The `MarkdownExtended` package can be simply call writing:

    // creation of the singleton instance of \MarkdownExtended\MarkdownExtended
    $content = \MarkdownExtended\MarkdownExtended::create()
        // get the \MarkdownExtended\Parser object passing it some options (optional)
        ->get('Parser', $options)
        // launch the transformation of a source string
        ->transform($source)
        // get the result content object
        ->getContent();

This will load in `$content` the parsed HTML version of your original Markdown `$source`.
To get the part you need from the content:

    echo $content->getBody();

NOTE - To keep the package compatible with old versions of Markdown, an interface is 
embedded with the common `Markdown($content)` function ; to use it, just include the file
`src/markdown.php`.

#### Command line usage

A short command line interface is proposed in the package running:

    ~$ bin/markdown_extended --help

This interface allows you to parse one or more files, extract some informations from sources,
write the results in files and some other stuff.

To generate a man-page from file `MANPAGE.md` with the interface itself, run:

    ~$ bin/markdown_extended -f man -o bin/markdown_extended.man MANPAGE.md
    ~$ man ./bin/markdown_extended.man

#### Apache handler usage

A sample of direct [Apache](http://www.apache.org/) handler is designed in the `demo/cgi-scripts/`
directory of the package. It allows you to automatically transform Markdown content files
in HTML thru a browser classic navigation. To learn more about this feature, please see the
dedicated [How-To](demo/Apache-Handler-HOWTO.md).


## Open-Source & Community

This plugin is a free software, available under [BSD license](http://en.wikipedia.org/wiki/BSD_licenses) ; 
you can freely use it, for yourself or a commercial use, modify its source code according
to your needs, freely distribute your work and propose it to the community, as long as you
let an information about its first authors.

As the sources are hosted on a [GIT](http://git-scm.com/) repository on
[GitHub](https://github.com/atelierspierrot/markdown-extended), you can modify it, to
ameliorate a feature or correct an error, by [creating your own fork](https://help.github.com/articles/fork-a-repo)
of this repository, modifying it and [asking to pull your modifications](https://github.com/atelierspierrot/markdown-extended/pulls)
on the original branch.

Please note that the "master" branch is **always the latest stable version** of the code. 
Development is done on branch "wip" and you can create a new one for your own developments.


## Development

To install all PHP packages for development, just run:

    ~$ composer install --dev

A documentation can be generated with [Sami](https://github.com/fabpot/Sami) running:

    ~$ php vendor/sami/sami/sami.php render sami.config.php

The latest version of this documentation is available online at <http://docs.ateliers-pierrot.fr/markdown-extended/>.


## Licenses

This software, as the original Markdown, is licensed under the terms of the
[BSD-3-Clause open source license](http://opensource.org/licenses/BSD-3-Clause).

You can use, transform and distribute this software and its dependencies as you wish, as
long as you mention the copyrights below:

    Mardown
    Copyright © 2004-2006, John Gruber
    http://daringfireball.net/
    All rights reserved.

    MultiMarkdown
    Copyright © 2005-2009 Fletcher T. Penney
    http://fletcherpenney.net/
    All rights reserved.

    PHP Markdown & Extra
    Copyright © 2004-2012 Michel Fortin
    http://michelf.com/projects/php-markdown/
    All rights reserved.

    Markdown Extended
    Copyright © 2008-2013 Pierre Cassat & contributors
    All rights reserved.

    Redistribution and use in source and binary forms, with or without modification, are
    permitted provided that the following conditions are met:

    - Redistributions of source code must retain the above copyright notice, this list of
      conditions and the following disclaimer.

    - Redistributions in binary form must reproduce the above copyright notice, this list
      of conditions and the following disclaimer in the documentation and/or other materials
      provided with the distribution.

    - Neither the name “Markdown” nor the names of its contributors may be used to endorse
      or promote products derived from this software without specific prior written permission.

    This software is provided by the copyright holders and contributors “as is” and any
    express or implied warranties, including, but not limited to, the implied warranties
    of merchantability and fitness for a particular purpose are disclaimed. In no event
    shall the copyright owner or contributors be liable for any direct, indirect,
    incidental, special, exemplary, or consequential damages (including, but not limited
    to, procurement of substitute goods or services; loss of use, data, or profits; or
    business interruption) however caused and on any theory of liability, whether in
    contract, strict liability, or tort (including negligence or otherwise) arising in
    any way out of the use of this software, even if advised of the possibility of such
    damage.
