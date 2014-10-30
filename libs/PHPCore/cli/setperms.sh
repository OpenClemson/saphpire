#!/bin/bash

# get current working directory.
sPwd=`pwd`

echo $sPwd

# sanity check.
if [[ "$sPwd" != *"libs/PHPCore/cli"* ]]
    then
        echo "Must be run inside CORE."
        exit 0
fi

# get back to vHost root.
cd "${sPwd}"/../../../

# get vhost root
sVHostRoot=`pwd`

# permissions
chmod -fv 775 "$sVHostRoot"
chmod -fv 775 "$sVHostRoot/classes"
chmod -fv 775 "$sVHostRoot/cli"
chmod -fvR 750 "$sVHostRoot/configs"
chmod -fv 775 "$sVHostRoot/css"
chmod -fv 775 "$sVHostRoot/docs"
chmod -fvR 750 "$sVHostRoot/files"
chmod -fv 775 "$sVHostRoot/img"
chmod -fv 775 "$sVHostRoot/includes"
chmod -fv 775 "$sVHostRoot/js"
chmod -fv 775 "$sVHostRoot/libs"
chmod -fvR 750 "$sVHostRoot/logs"
chmod -fv 750 "$sVHostRoot/media"
chmod -fv 775 "$sVHostRoot/services"
chmod -fv 775 "$sVHostRoot/templates"
chmod -fv 775 "$sVHostRoot/tests"
chmod -fv 775 "$sVHostRoot/configs/app.xml"
chmod -fv 775 "$sVHostRoot/configs/contacts.xml"
chmod -fv 775 "$sVHostRoot/configs/hosts.xml"
chmod -fv 775 "$sVHostRoot/.htaccess"
chmod -fv 775 "$sVHostRoot/config.php"
chmod -fv 775 "$sVHostRoot/index.php"

# ownership
chown -fv apache:webdev "$sVHostRoot"
chown -fv apache:webdev "$sVHostRoot/classes"
chown -fv apache:webdev "$sVHostRoot/cli"
chown -fvR apache:webdev "$sVHostRoot/configs"
chown -fv apache:webdev "$sVHostRoot/css"
chown -fv apache:webdev "$sVHostRoot/docs"
chown -fvR apache:webdev "$sVHostRoot/files"
chown -fv apache:webdev "$sVHostRoot/img"
chown -fv apache:webdev "$sVHostRoot/includes"
chown -fv apache:webdev "$sVHostRoot/js"
chown -fv apache:webdev "$sVHostRoot/libs"
chown -fvR apache:webdev "$sVHostRoot/logs"
chown -fv apache:webdev "$sVHostRoot/media"
chown -fv apache:webdev "$sVHostRoot/services"
chown -fvR apache:webdev "$sVHostRoot/templates"
chown -fv apache:webdev "$sVHostRoot/tests"
chown -fv apache:webdev "$sVHostRoot/configs/app.xml"
chown -fv apache:webdev "$sVHostRoot/configs/contacts.xml"
chown -fv apache:webdev "$sVHostRoot/configs/hosts.xml"
chown -fv apache:webdev "$sVHostRoot/.htaccess"
chown -fv apache:webdev "$sVHostRoot/config.php"
chown -fv apache:webdev "$sVHostRoot/index.php"