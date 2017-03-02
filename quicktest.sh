#!/bin/bash
clear
/usr/bin/mysql owp_users < ./schema/owp-complete.sql
./vendor/bin/phpunit --verbose --colors=never
