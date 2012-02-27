## Syntax for tables

The first table syntax was (*as I know*) introduced by Michel Fortin in he's *Markdown Extra* version. He imagines a visual syntax like:

    | First Header  | Second Header |
    | ------------- | ------------: |
    | Content Cell  | Content Cell  |
    | Content Cell  | Content Cell  |

The rules here are that every table's line is written alone on a single line, the very first line is the header (*thead*) of the table, followed by a mandatory separators line of hyphens `-` and then each line of the table content on a single line (*tbody*). Columns are separated by pipes `|` and each line may have the same number of pipes.

Spacing is not important except for visual facility and we can write our tables without the leading pipes.

This syntax is basic but it feets the original Markdown's goal (*to keep a file content human readable*). Michel has constructed an advanced feature to manage alignment in columns by using colons `:` in the separators line :

- a colon on the left of a separator's cell means a left-aligned column : `:---`,
- a colon on the right of a separator's cell means a right-aligned column : `---:`,
- two colons on the left and the right of a separator's cell means a centered column : `:--:`.

