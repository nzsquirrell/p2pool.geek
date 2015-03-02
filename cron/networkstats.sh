#!/bin/sh
/usr/bin/timeout 30 \
/pathtophp/php /pathtocron/cron/networkblocks.php \
>>/pathtocron/cron/networkstats.log 2>&1
