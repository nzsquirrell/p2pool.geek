#!/bin/sh
/usr/bin/timeout 300 \
/pathtophp/php /pathtocron/cron/runjobs.php \
>>/pathtocron/cron/runjobs.log 2>&1
