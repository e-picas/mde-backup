----

**Progress of this feature:**

> **OK, except that attributes can not be multi-lines**

----

## Syntax for links

Links can be *inline* in the text, like:

    This is a paragraph with a [link to a test page](http://test.com/ "My optional title") for now ...

The rule here is to write the link text between brackets. Then, between parenthesis, the URL of the link, relative or asbolute, and an optional title wrapped in double-quotes.

Using this notation is the basic syntax for links. But it can make the file not easy to read, which is the first goal of Markdown.

So we can use **references** for links. This allows us to keep the URL and other informations about the link outside the content. For example:

    This is a paragraph with a [referenced link][linkid]. I can continue my content 
    clearly because it is still readable for human eyes ...

    [linkid]: http://test.com/ "My optional title"

The link here in the final content will be exactly the same as above. The point is just that the informations are not in the content but after it.

A new feature introduced by Fletcher Penney in he's *Multi Markdown* version is the possibility to add attributes in references. Doing so, we can add, after the optional title, any attributes constructed like couples of pair `variable/value` with or without double-quotes. For example:

    This is a paragraph with a [referenced link][linkid]. I can continue my content 
    clearly because it is still readable for human eyes ...

    [linkid]: http://test.com/ "My optional title" class=mylinkclass style="border:1px solid black"

As I said, the class will produce an link tag like:

    <a href="http://test.com/" title="My optional title" 
        class="mylinkclass" style="border:1px solid black">
            referenced link</a>

For now, you may write the entire reference definition on a single line. This is not the case in Multi Markdown, which allows to pass a line, but I can't get this feature working for now. This may be one of the evolutions ...

## PCRE masks used for links

