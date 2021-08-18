#!/bin/bash

envKeysForFrontend=("REACT_APP_API_URL" "REACT_APP_SHOW_MOCK_LOGIN")

folder=${1:-"./build"}
filePath=$folder"/env-config.js"

echo "updating $filePath with current environment variables"
# Recreate config file
rm -rf $filePath
touch "$filePath"

# Add assignment
echo "window._env_ = {" >> $filePath

for envVarKey in "${envKeysForFrontend[@]}"
do
  :
  # Read value of current variable if exists as Environment variable
  value=$(printf '%s\n' "${!envVarKey}")
  # If there is a value output it to file
  [[ $value ]] && echo "  $envVarKey: \"$value\"," >> $filePath
done

echo "}" >> $filePath
