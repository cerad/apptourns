http://stackoverflow.com/questions/8677830/what-is-wrong-with-control-characters-in-phpunit-command-line-tool/15299204#15299204

cd ~/bin

#!/bin/sh
/xampp/php/phpunit "$@" 2>&1 | perl -pe 's/(?<=\e\[)2;//g'
