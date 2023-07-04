#!/bin/bash

cd "$(dirname "$0")"

default_ip="127.0.0.1"
ip=${1:-$default_ip}
root=${EDLIB_ROOT_DOMAIN:-edlib.test}

read -r -d '' hosts << EOM
$ip\tapi.$root
$ip\tca.$root
$ip\tdocs.$root
$ip\tfacade.$root
$ip\thub.$root
$ip\tmailpit.$root
$ip\tnpm.components.$root
$ip\twww.$root
EOM

replaceStringWithoutNewline=${hosts//$'\n'/\\n}
echo $replaceStringWithoutNewline
sudo ./manage-block-in-file.sh /etc/hosts "$replaceStringWithoutNewline"

