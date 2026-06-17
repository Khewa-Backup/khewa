#!/bin/bash

# https://khewa.com/en/module/hspointofsalepro/searchCron?id_shop=1&full=1&sub-tab=setup-customer

# /home/j8m1o8hr4gjg/public_html/crons/rockposcornci.sh

TOTALCUSTS=`curl -s -X GET https://khewa.com/crons/rockposcustindex.php`
PP=100
TOTALP=$((($TOTALCUSTS|bc+$PP-1)/$PP))

echo $TOTALCUSTS
echo $TOTALP

if [ $TOTALP -gt 0 ]
then
    pcount=0

    while [ $pcount -le $TOTALP ]
    do
        offset=`expr $pcount \* $PP`
        pcount=$(expr $pcount + 1)
        
        resp=$(curl -s -X POST -d "{'ajax':1,'offset':$offset}" "https://khewa.com/en/module/hspointofsalepro/searchCron?id_shop=1&full=1&sub-tab=setup-customer")
        echo $offset;
        echo $resp;
    done
    echo "Customer Index Completed!"
fi