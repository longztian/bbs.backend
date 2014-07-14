#!/bin/bash

serverdir=/home/web/www.houstonbbs.com/server
out_file=ControllerRouter.php

tmp_file=/tmp/$out_file

function error_exit
{
    ## do clean up
    rm -rf $tmp_file

    ## print error message
    echo "Error: $1"
    exit 1;
} 1>&2

rm -rf $tmp_file || error_exit "failed to initialize $tmp_file"

cat > $tmp_file <<'EOF'
<?php

//!!!
//!!!  do not edit, generated by script/build_route.sh
//!!!

namespace site;

use site\ControllerFactory;

/**
 * Description of ControllerRouter
 *
 * @author ikki
 */
class ControllerRouter extends ControllerFactory
{

   protected static $_route = [
EOF

for i in $serverdir/controller/*/*Ctrler.php; do
    ctrler=$(echo $i | sed -e "s!^$serverdir/controller/!!" -e 's!.php$!!')
    uri=$(echo $ctrler | sed 's!Ctrler$!!' | tr '[:upper:]' '[:lower:]' | awk -F '/' '{ if($1 == $2) print $1; else print $1"/"$2; }')

    echo \'$uri\'' => '\''site\\controller\\'$(echo $ctrler | sed 's!/!\\\\!g')\'','
done | sort -t \' -k 2,2 | column -t | sed 's/^/      /' >> $tmp_file

cat >> $tmp_file <<'EOF'
   ];

}

//__END_OF_FILE__
EOF

if [[ ! -z "$(diff $tmp_file $serverdir/$out_file || echo 'Failed to diff')" ]]; then
    mv -f $serverdir/$out_file $serverdir/$out_file.backup && mv -f $tmp_file $serverdir/$out_file
else
    echo "$out_file file not changed, skip updating"
    rm -rf $tmp_file
fi
