#!/bin/bash

echo -n 'removing existing doc ...'
rm -Rf ./phpdoc
echo 'OK'

echo -n 'launching pphpdoc ...'
#phpdoc run -d . -t ./phpdoc --force \
#	--sourcecode -p --ignore src/Markdown_Extra/* \
#	--title MVCMS --defaultpackagename ApplicationFoundations;
phpdoc
echo 'OK'

echo -n 'setting phpdoc directory readable ...'
chmod 755 ./phpdoc
echo 'OK'

echo '-- finish :)'

exit