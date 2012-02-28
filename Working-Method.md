### A library of Markdown parsers

First, I added a full set of the Markdown parsers I use :

-   the very first original Perl version by **John Gruber** (`Markdown_1.0.1/markdown.pl`),
-   the original PHP version by **Michel Fortin** (`PHP_Markdown_1.0.1o/markdown.php`),
-   the extended "Extra" PHP version by **Michel Fortin** (`PHP_Markdown_Extra_1.2.5/markdown.php`),
-   finally, the extended "Multi" Perl version by **Fletcher Penney** (`MultiMarkdown/markdown.pl`).


### A document of full Markdown syntaxes

Then I wrote a document with a full set of Markdown syntaxes, one part for each above versions of Markdown parser: file `MD_syntax.md` (*I often use the `md` file extension for Markdown, but it is optional, you can use any text extension*).


### A PHP interface

Finally, file `index.php` is a small interface to parse and visualize results. It basically present a navigation menu to switch the parser to use and display the result of this parsing.


### A search on the web for interesting new features

For this work, I also benefited of some other works on Markdown:

-   some interesting HTML5 features added by [**Egil Hansen**](http://egilhansen.com) in his [php-markdown-extra-extended](https://github.com/egil/php-markdown-extra-extended) version,
-   an *object oriented* version refound by [**Max Tsepkov**](http://www.garygolden.me) in his [markdown-oo-php](https://github.com/garygolden/markdown-oo-php) version.


**That's it!**