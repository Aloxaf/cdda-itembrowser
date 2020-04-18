#!/bin/sh
wget https://github.com/CleverRaven/Cataclysm-DDA/archive/master.zip
unzip master.zip
echo "#define VERSION \"$(date -u +'%Y-%m-%dT%H:%M:%SZ')\"" > Cataclysm-DDA-master/src/version.h
msgfmt Cataclysm-DDA-master/lang/po/zh_CN.po -o locale/zh_CN/LC_MESSAGES/cataclysm-dda.mo
# sed -i 's/obsolete": true/obsolete": false/' Cataclysm-DDA-master/data/mods/*/modinfo.json
sudo -u www-data php src/artisan cache:clear
sudo -u www-data php src/artisan cataclysm:rebuild Cataclysm-DDA-master/
# cp locale/zh_CN/LC_MESSAGES/cataclysm-dda.mo /usr/share/locale/zh_CN/LC_MESSAGES/