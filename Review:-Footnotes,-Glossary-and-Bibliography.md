----

**Progress of this feature:**

> **OK, except for bibliographic references in text (todo: manage a two handed reference)**


**Todo:**

> **let the user configure the different renderings ...**

----

## The meaning of footnotes

Footnotes in a text are a way to increase the meaning of it, the external references, some required definitions for the comprehension of that text without overcroweded it ... They are often used in books and official contents.

On this page we gonna try to explain footnotes usage and differences between **a simple footnote**, a short additional content that would have no place in the content, **a glossary note**, an explanation about a technical term for example, and **a bibliographic note**, which refer to another book or work, hardly referenced to let the lector find the source.

### Footnote

As we said, footnotes are just snippets of additional informations that seems not necessary in the content. For example, if we talk about *Linux* in text about a specific computer, this may not make sense to cite *Linus Trovalds* in the content. But we do want Linus'name to be present in our work, so we add a little note attached to the term "Linux", which can be:

> *An open source operating system created by Linus Trovalds.*

### Glossary

A glossary note is most like a definition. It is attached to a specific term and try to give one or more explanations of it. Glossary notes have to be considered as *definitions list* in HTML, except that they will all be placed like footnotes at the end of the content.

### Bibliography

A bilbliographic note is a fully referenced external work. This kind of notes is often used in academic or scientific work. The point is that we have to follow some *academic rules* for bibliographic notes, such as naming the authors, writting the title of the work in italic, exactly as it has been published, and cite enough informations (*such as the edition*) to let the lector find this work easily.

## Syntax for footnotes

The default syntax for footnotes has been imagine by Michel Fortin in he's *Markdown Extra* version, and is:

    That's some text with a footnote[^1] attached ...

    [^1]: And that's the footnote.

We simply write a marker in content, constructed as the reference of the note (*which can be a number or a text*) preceeding by a circumflex `^` and between brackets. Then we write, anywhere in the document, the content of the note like a reference on a new line. That note content should have other Markdown tags such as emphasis, and can be written on multiple lines, as long as you do not pass a blank line.

Fletcher Penney has added the glossary possibility, just refounding the construction of the note content:

    My text with a footnote ref [^glossaryfootnote].

    [^glossaryfootnote]: glossary: term (optional sort key)
	The actual definition belongs on a new line, and can continue on
	just as other footnotes.

The point here is that the content always starts with `glossary:`. Then we write the term to be defined, followed by an optional *short key* which will be used to later the sorting order of the glossary. Then the definition is on a new line.

Fletcher also imagines a way to define bibliographic notes:

    This is a statement that should be attributed to its source[p. 23][#Doe:2006].

    [#Doe:2006]: John Doe. *Some Big Fancy Book*.  Vanity Press, 2006.

As we can see, the circumflex is replaced by a sharp `#` and the marker is two-parts handlhed.

## PCRE masks used for notes
