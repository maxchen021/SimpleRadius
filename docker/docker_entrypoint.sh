#!/bin/bash

INSTALL_STATUS_FILE=/etc/SimpleRadius/database/installed.txt

if [ -f $INSTALL_STATUS_FILE ];
then
   /etc/SimpleRadius/scripts/simple_radius.php Restore_Config_Files_From_DB
else
   /etc/SimpleRadius/scripts/simple_radius.php Factory_Reset
   touch $INSTALL_STATUS_FILE
fi

echo 'Simple Radius started successfully'

apache2_fail_count=0
freeradius_fail_count=0
max_fail_count=3

while true;
do
    if [ `ps -ef | grep apache2 | grep -v grep | wc -l` -eq 0 ]; then
        apache2_fail_count=$(( $apache2_fail_count + 1 ))
    fi

    if [ `ps -ef | grep freeradius | grep -v grep | wc -l` -eq 0 ]; then
        freeradius_fail_count=$(( $freeradius_fail_count + 1 ))
    fi

    if [ $apache2_fail_count -gt $max_fail_count ]; then
        echo "Error: apache2 failed more than ${max_fail_count} time"
        exit
    fi

    if [ $freeradius_fail_count -gt $max_fail_count ]; then
        echo "Error: freeradius failed more than ${max_fail_count} time"
        exit
    fi

    sleep 60
done
