#!/bin/bash

for file in `find /home/projects/data/pbr-wifc-mts-money/ -type f -name '*.log' -print`
do
  dt=`date +%Y-%m-%d_%H_%M`
  mv $file $file.$dt
  gzip -9 $file.$dt
done

find /home/projects/data/pbr-wifc-mts-money/ -name '*.log.gz' -mtime +100 -type f | xargs rm -f