----

**Progress of this feature:**

> **Partial:**
>
> -   table content can not be separated in multi-body sections
> -   caption can just be written **before** the table

----

## Syntax for tables

The first table syntax was (*as I know*) introduced by Michel Fortin in he's *Markdown Extra* version. He imagines a visual syntax like:

    | First Header  | Second Header |
    | ------------- | ------------: |
    | Content Cell  | Content Cell  |
    | Content Cell  | Content Cell  |

The rules here are that every table's line is written alone on a single line. The very first line is the header of the table (*thead*), followed by a mandatory separators line of hyphens `-`. Then, each line of the table content is written on a single line (*tbody*). Columns are separated by pipes `|` and each line may have the same number of pipes. Spacing is not important except for visual facility and we can write our tables without the leading pipes. Finally, the content of the cells can have other Markdown span features like emphasis.

This syntax is basic but it feets the original Markdown's goal (*to keep a file content human readable*).

Michel has constructed an advanced feature to manage **alignment in columns** by using colons `:` in the separators line :

- a colon on the left of a separator's cell means a left-aligned column : `:---`
- a colon on the right of a separator's cell means a right-aligned column : `---:`
- two colons on the left and the right of a separator's cell means a centered column : `:--:`

Fletcher Penney, in he's *Multi Markdown* version, pushed the table structure one step higher by allowing **multi-header lines** and **multi-columns cells**. Let's look an example :

    |               | Grouping                    ||
    | First Header  | Second Header | Third header |
    | ------------- | ------------: | :----------: |
    | Content Cell  |  *Long Cell*                ||
    | Content Cell  | **Cell**      | **Cell**     |

The result here will be a two lines header and, for example in the first line, a second cell containing "Grouping" on two columns (*this will be build in HTML with the attribute `colspan=2`*).

The point, simple to understand and use, is that we write as many pipes (*without spaces*) as our content must cover columns. Easy and powerful ...

Another new feature of Fletcher's work is that we can precise a **caption** for our table. To do so, we just write it between brackets just before or just after the table, on a single line. In the above example, it can be:

    |               | Grouping                    ||
    | First Header  | Second Header | Third header |
    | ------------- | ------------: | :----------: |
    | Content Cell  |  *Long Cell*                ||
    | Content Cell  | **Cell**      | **Cell**     |
    [ my table caption ]

Finally, Fletcher's imagines an high level of HTML construction allowing to write separate sets of content for each table, separating them by a blank line:

    |               | Grouping                    ||
    | First Header  | Second Header | Third header |
    | ------------- | ------------: | :----------: |
    | Content Cell  |  *Long Cell*                ||
    | Content Cell  | **Cell**      | **Cell**     |
    
    | New section   |   More        |         Data |
    | And more      |           And more          ||

The result will be a table with two `tbody` sections.

## PCRE masks used for tables
