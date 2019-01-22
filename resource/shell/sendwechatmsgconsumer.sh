#!/bin/bash
FilePath=/www/htdocs/MyFramework/app/Console
php=/usr/local/php/bin/php
pname="send_wechat_msg_consumer"

count=`ps -ef |grep "index.php"| grep -v "grep" | grep "${pname}" | wc -l`

if [ $count -lt 1 ]; then
ps -eaf |grep "index.php"|grep "${pname}" | grep -v "grep"| awk '{print $2}'|xargs kill -9
sleep 2
ulimit -c unlimited

cd  ${FilePath}
${php} index.php ${pname}

sleep 2
fi

exit 0
