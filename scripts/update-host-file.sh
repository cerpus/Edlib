#!/bin/bash

cd "$(dirname "$0")"

default_ip="127.0.0.1"
ip=${1:-$default_ip}

read -r -d '' hosts << EOM
$ip\tedlib.internal.url.local
$ip\tedlib.internal.auth.local
$ip\tedlib.internal.resource.local
$ip\tedlib.internal.lti.local
$ip\tedlib.internal.doku.local
$ip\tedlib.internal.common.local
$ip\tedlib.internal.version.local
$ip\tedlibfacade.local
$ip\ttest.edlibfacade.local
$ip\tlocalhost
$ip\tcontentauthor.local
$ip\tca.edlib.local
$ip\tapi.edlib.local
$ip\twww.edlib.local
$ip\tnpm.components.edlib.local
EOM

replaceStringWithoutNewline=${hosts//$'\n'/\\n}
echo $replaceStringWithoutNewline
sudo ./manage-block-in-file.sh /etc/hosts "$replaceStringWithoutNewline"

