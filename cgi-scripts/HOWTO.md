Extended Markdown Apache module - HOW TO
========================================


This document explains the usage of the `emd_apacheHandler.sh` shell script to use with [Apache](http://www.apache.org/).
This script is designed to handle Markdown syntax content files and serve to a webserver a parsed HTML
version of the original content.


## Prerequisite

To allow Apache to use this script, your webserver must run at least version 2 of Apache with the following modules
enabled:

-   [mod_rewrite](http://httpd.apache.org/docs/2.2/en/mod/mod_rewrite.html)
-   [mod_actions](http://httpd.apache.org/docs/trunk/en/mod/mod_actions.html)
-   [mod_mime](http://httpd.apache.org/docs/2.2/en/mod/mod_mime.html)

To learn more about Apache's module enabling, see <http://wiki.apache.org/httpd/DebianLikePlatform>.


## Apache configuration

To ask Apache to handle Markdown files with the parser, you must define the following directives in your
`.htaccess` file (*or create a new one if it doesn't exist*). This `htaccess` file must involve at least 
the root directory of your Markdown files AND the shell handler itslef. If your architecture doesn't allow
you to use a single `htaccess` file for both your documents and the handler, you will have to define both
`.htaccess` files, one for each concerned directory.


### Sample htaccess file

    # We autorize CGIs
    Options +ExecCGI

    # We include 'sh' in exec scripts
    AddHandler cgi-script .sh

    # To display '.md' files as text if something went wrong
    AddType text/html .md

    # Here you can define some custom configuration variables used by the parser
    #SetEnv EMD_TPL /{ SERVER ABSOLUTE PATH TO }/template_file.html

    # Treat '.md' files by the Markdown handler
    # You can add any extension(s) you want to parse at the end of the line, separated by space
    # CAUTION - this requires to know exactly where the CGI is ...
    AddHandler MarkDown .md
    Action MarkDown /{ SERVER ABSOLUTE PATH TO }/extended-markdown/cgi-scripts/emd_apacheHandler.sh virtual


**CAUTION** - The server pathes used in these directives must be related to your server's Apache
`DOCUMENT_ROOT` (*and not to your `/` filesystem root*).

**NOTE** - The default `.htaccess` files of this package are designed to work with a copy or a clone
of the GIT repository at `/GitHub_projects/extended-markdown/` from the Apache `DOCUMENT_ROOT`.
