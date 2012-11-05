<!--  This file was automatically generated with PHP_Extended_Markdown_Builder class on {%DATE%} //-->

# PHP Extended Markdown : DEVELOPER MANUAL

## About GAMUTS

The Extended Markdown process uses different piles of methods called *Gamuts*, used for
different actions and at different parsing events.

    initGamut : parsed at Extended Markdown instance creation
    setupGamut : parsed very first at each `transform()` method call to prepare variables
    documentGamut : parsed on the whole document
    spanGamut : parsed on every span blocks
    blockGamut : parsed on the whole document after the `documentGamut`
    htmlBlockGamut : a special Gamut parsed on HTML blocks
    teardownGamut : parsed last at each `transform()` method call to clean variables

{%DEV_MANUAL%}

>    PHP Extended Markdown
>    Copyright (c) 2012 Pierre Cassat
>   
>    original MultiMarkdown
>    Copyright (c) 2005-2009 Fletcher T. Penney
>    <http://fletcherpenney.net/>
>   
>    original PHP Markdown & Extra
>    Copyright (c) 2004-2012 Michel Fortin  
>    <http://michelf.com/projects/php-markdown/>
>   
>    original Markdown
>    Copyright (c) 2004-2006 John Gruber  
>    <http://daringfireball.net/projects/markdown/>
