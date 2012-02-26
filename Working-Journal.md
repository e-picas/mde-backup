Well, here is the point ...

**First, just have a look on our reference document at [MD_syntax.md](https://github.com/PieroWbmstr/Full_PHP_Markdown/blob/master/MD_syntax.md) in the repository. This is my base working file that is parsed by the parsers to test results.**

**I will try here to write my thoughts about this work and the difficulties I faced ... As I am not very at ease with PCRE patterns and replacements, there may be a lot!**

## First step: select the "right" syntaxes

The first difficulty was to choose in older Markdown versions the syntaxes rules and features that seems to me the most relevants and usefulls.

I used to work as a corrector for french texts so I am quite friend with typographic and syntaxic rules that are mostly use, mainly in journalism or administration. Some features that I found very usefull in the *Multi Markdown* version was the **glossary notes** and **bibliographic references**.

I also found very usefull the complex **HTML tables structure** allowed by Fletcher's version. It permit to build some complex tables, easy to read in the file and with a deep xHTML meaning in the code.

Another thing interesting in that version, what is, in my opinion, a lack in others, is that you can **define attributes for images and links**. This push higher the opportunity to build a pretty HTML page by choosing images sizes for example, adding some CSS styles to links etc.

What was missing, for me, in Fletcher's version that I found in *Markdown Extra*, was the possibility to write some **definitions lists** and parse them automatically. This is not often used in web pages, but definition lists have a special meaning in HTML ...

Also interestring in Michel's version is the **abbreviations**. They are automatically transformed in content (*and striped from text*) in xHTML abbreviation tag, which add some meaning to the context.

Finally, the idea of the **fenced code blocks** in Michel's version is not so bad. I never use this feature but it can be usefull for any reason.

OK. A complete syntax rules to propose in my new version can now be made:
-   complex tables structure,
-   attributes for links and images,
-   glossary and bibliography footnotes,
-   definition lists,
-   abbreviations,
-   fenced code blocks.

One point I can't solve for now is the case of in-page anchors. Fletcher's version allows to create automatic anchors on any title, Michel's version allows to write them specifically in text, wrapped between curly brackets. This is the first point to discuss, analyze and test ...