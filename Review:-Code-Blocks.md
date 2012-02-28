----

**Progress of this feature:**

> **OK, with fenced feature and optionnaly the language of the code**

----

## Syntax for code blocks

A basic code block can be written starting each line by 4 spaces:

    This is a classic paragraph ...

        and this is a "pre formatted" code block, wrapped in <pre><code> HTML tags

You can include any other Markdown tag in a code block, as long as each line starts with 4 spaces.

Michel Fortin, in his *Markdown Extra* version, added a special writing rule to create code blocks without spaces at the starting of the lines. He imagines to wrap the block content between two lines of 3 or more tildes `~`.

    ~~~~
    My code here
    ~~~~

This way you can write some code blocks not counting every spaces of each line (...).

I've found not so long ago an interesting feature added to this fenced syntax imagines by [**Egil Hansen**](    http://egilhansen.com) for his [php-markdown-extra-extended](https://github.com/egil/php-markdown-extra-extended) version to create some *language-friendly* code blocks, as it is [preconize by the W3C](http://dev.w3.org/html5/spec-author-view/the-code-element.html#the-code-element) in the HTML5 specifications. To do so, we just have to write our language just at the end of the first tildes line.

    ~~~~html
    My code here
    ~~~~

Be carefull here, a difference with Egil's version in *Extended Markdown* is that your language name must be just after the last tilde, with no space.

The code above will produce:

    <pre><code class="language-html">My code here</code></pre>


## PCRE masks used for code blocks

