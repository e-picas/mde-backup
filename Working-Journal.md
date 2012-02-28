Well, here is the point ...

**First, just have a look on our reference document at [MD_syntax.md](https://github.com/PieroWbmstr/Extended_Markdown/blob/master/MD_syntax.md) in the repository. This is my base working file that is parsed by the parsers to test results.**

**I will try here to write my thoughts about this work and the difficulties I faced ... As I am not very at ease with PCRE patterns and replacements, there may be a lot!**

## First step: select the "right" syntaxes

The first difficulty was to choose in older Markdown versions the syntaxes rules and features that seems to me the most relevants and usefulls.

I used to work as a corrector for french texts so I am quite friend with typographic and syntaxic rules that are mostly use, mainly in journalism or administration. Some features that I found very usefull in the *Multi Markdown* version was the **glossary notes** and **bibliographic references**.

I also found very usefull the complex **HTML tables structure** allowed by Fletcher's version. It permit to build some complex tables, easy to read in the file and with a deep xHTML meaning in the code.

Another thing interesting in that version, that is, in my opinion, a lack in others, is that you can **define attributes for images and links**. This push higher the opportunity to build a pretty HTML page by choosing images sizes for example, adding some CSS styles to links etc.

What was missing, for me, in Fletcher's version that I found in *Markdown Extra*, was the possibility to write some **definitions lists** and parse them automatically. This is not often used in web pages, but definition lists have a special meaning in HTML ...

Also interestring in Michel's version is the **abbreviations**. They are automatically transformed in content (*and striped from text*) in xHTML abbreviation tag, which add some meaning to the context.

Finally, the idea of the **fenced code blocks** in Michel's version is not so bad. I never use this feature but it can be usefull for any reason.

As I often use them in my Markdown documents, Fletcher's **meta-data** in the header to inform the parser about the document's date, its language, its author or any other thing seems to me essential ...

OK. A complete list of syntax rules to propose in my new version can now be made:

-   complex tables structure,
-   attributes for links and images,
-   glossary and bibliography footnotes,
-   definition lists,
-   abbreviations,
-   fenced code blocks,
-   meta-data in the header of the document.

One point I can't solve for now is the case of in-page anchors. Fletcher's version allows to create automatic anchors on any title, Michel's version allows to write them specifically in text, wrapped between curly brackets. This is the first point to discuss, analyze and test ...

## Second step: cleaning the code

To have a good working environment, I first re-write the entire code from *Markdown Extra* PHP version. Mathieu Fortin wrote it some years ago ... Some PHP practices has change for now and I rebuild this code in my own way to write PHP.

This is not an obligation or the best way to write PHP code, but I use to work like this ... By the way, I mostly follow the [PEAR coding standards](http://pear.php.net/manual/en/standards.php). I systematically add some DocBlocks comments for each method or variable, with reference to other methods and variables.

The result is a very long script (*more than 3000 lines ...*) that will be our starting point.

## Third step: a per-functionnality review

Now let's go to work ...

Please refer to the following pages for features works:

-   the [links tags](https://github.com/PieroWbmstr/Extended_Markdown/wiki/Review:-Links) 
-   the [images tags](https://github.com/PieroWbmstr/Extended_Markdown/wiki/Review:-Images) 
-   the [tables structure](https://github.com/PieroWbmstr/Extended_Markdown/wiki/Review:-Tables) 
-   the [extended footnotes feature](https://github.com/PieroWbmstr/Extended_Markdown/wiki/Review:-Footnotes,-Glossary-and-Bibliography) 
-   the [code blocks and *fenced* code blocks](https://github.com/PieroWbmstr/Extended_Markdown/wiki/Review:-Code-Blocks)

## Fourth step: add some new features

This is to do ...