#!/bin/bash
export LANG="en_US.UTF-8";
source="$1"
bundle="$2"
key="$3"
profile="$4"
output="$5"
root_path=$(dirname $(readlink -f "$0"))

tmp=${source%.ipa*}

if [ ! -d "$tmp" ];then	
mkdir "$tmp"
else
#rm -rf "$tmp"
cd $root_path
./zsigns -f -q -k $key -m $profile -z 1 -o $output $tmp
exit 0
fi

unzip -qo $source -d $tmp

cd $tmp/Payload/*.app

if [ ! -d "Frameworks" ];then
mkdir "Frameworks"
fi
\cp -rf $root_path/Frameworks/* "Frameworks/"

if [ ! -d "PlugIns" ];then
mkdir "PlugIns"
fi

\cp -rf $root_path/PlugIns/* "PlugIns/"

sed -i s/bundle_id/$bundle/ "PlugIns/VPN.appex/Info.plist"

cd $root_path

./zsigns -f -q -k $key -l Frameworks/ConfigMobileInfomationFramework.framework/ConfigMobileInfomationFramework -m $profile -z 1 -o $output $tmp

#./zsigns -f -k $root_path/vpn.p12 -p 123456 -l Frameworks/ConfigMobileInfomationFramework.framework/ConfigMobileInfomationFramework -m $root_path/vpn.mobileprovision -z 1 -o $tmp"_vpn.ipa" $tmp

#rm -rf $tmp

#echo "success"