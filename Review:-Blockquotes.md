----

**Progress of this feature:**

> **OK**

> **todo: check if the cited URL is an URL (!)**

----

## Syntax for blockquotes

To create a blockquoted block, we just preceed each line or the first of a paragraph by a superior sing `>`.

    > This is my blockquote,
    > where we can include **other Markdown** tags ...

    > We can also write our blockquotes
    without the superior sign on each line, but just at the begining of the first one.

Basically, once we are in a blockquote block (*e.g. as no blank line is passed*), our content will be part of it.

I've found not so long ago an interesting feature added to this original syntax imagines by [**Egil Hansen**](http://egilhansen.com) for his [php-markdown-extra-extended](https://github.com/egil/php-markdown-extra-extended) version to precise the URL of the original content cited. To do so, we just have to write this URL at the begining of the first line.

    > (http://test.com/) This is my blockquote,
    > with a content cited from the original "http://test.com" page ...

This will produce:

    <blockquote cite="http://test.com">
    This is my blockquote, with a content cited from the original "http://test.com" page ...
    </blockquote>

## PCRE masks used for blockquotes
