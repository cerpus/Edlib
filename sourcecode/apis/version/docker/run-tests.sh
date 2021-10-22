#!/bin/bash
rm target/surefire-reports/*.xml

mvn test
exitcode=$?

cp target/surefire-reports/*.xml /result/
chown -R ${RUN_AS_UID:-0} /result/

exit $exitcode