#!/bin/bash

INSTALL_STATUS_FILE=/etc/SimpleRadius/installed.txt

if [ -f $INSTALL_STATUS_FILE ];
then
   /etc/SimpleRadius/scripts/simple_radius.php Restart_System
else
   /etc/SimpleRadius/scripts/simple_radius.php Factory_Reset
   touch $INSTALL_STATUS_FILE
fi

echo 'Simple Radius started successfully'

httpd_fail_count=0
radiusd_fail_count=0
max_fail_count=3

while true;
do
    if [ `ps -ef | grep httpd | grep -v grep | wc -l` -eq 0 ]; then
        httpd_fail_count=$(( $httpd_fail_count + 1 ))
    fi

    if [ `ps -ef | grep radiusd | grep -v grep | wc -l` -eq 0 ]; then
        radiusd_fail_count=$(( $radiusd_fail_count + 1 ))
    fi

    if [ $httpd_fail_count -gt $max_fail_count ]; then
        echo "Error: httpd failed more than ${max_fail_count} time"
        exit
    fi

    if [ $radiusd_fail_count -gt $max_fail_count ]; then
        echo "Error: raiusd failed more than ${max_fail_count} time"
        exit
    fi

    sleep 60
done
