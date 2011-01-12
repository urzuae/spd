#!/usr/local/bin/bash

cd /home2/phpnuke/html/vw/;

top -b |grep mysql|cut -b64-|cut -d "%" -f1|awk '{sum+=$1;}END{print strftime("%Y-%m-%d,%H:%M,", systime()) sum >> "load_mysql.log"}';
top -b |grep httpsd|cut -b64-|cut -d "%" -f1|awk '{sum+=$1;}END{print strftime("%Y-%m-%d,%H:%M,", systime()) sum >> "load_httpsd.log"}';
