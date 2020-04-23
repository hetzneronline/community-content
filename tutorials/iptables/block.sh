#!/bin/bash
###PUT HERE COMA SEPARATED LIST OF COUNTRY CODE###
COUNTRIES=(de at)
WORKDIR=$(mktemp -d)
#######################################
for i in "${COUNTRIES[@]}"; 
do
curl http://www.ipdeny.com/ipblocks/data/countries/$i.zone >> $WORKDIR/iptables-blocklist.txt;
done

if [ -f $WORKDIR/iptables-blocklist.txt ]; then
  iptables -F
  BLOCKDB="$WORKDIR/iptables-blocklist.txt"
  IPS=$(grep -Ev "^#" $BLOCKDB)
  for i in $IPS
  do
    echo iptables -A INPUT -s $i -j DROP >> input.sh
    echo iptables -A OUTPUT -d $i -j DROP >> output.sh
  done
fi
rm -r $WORKDIR