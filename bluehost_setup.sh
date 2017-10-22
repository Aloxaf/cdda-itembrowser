#!/bin/sh
# exit on error
set -e

# get the absolute path to the data files
BASE_PATH=$(cd $(dirname $0 ) && pwd )
STORAGE_PATH="src/app/storage"

cd "$BASE_PATH"
cp bluehost_composer.json src/composer.json
cp bluehost_artisan src/artisan
# download the cataclysm dda's source code
if [ ! -e master.zip ]
then
    echo "Downloading game source and data files..."
    curl -LOs https://github.com/CleverRaven/Cataclysm-DDA/archive/master.zip
fi

echo "Unzipping..."
unzip -qo master.zip
cd Cataclysm-DDA-master
cd src
datetime="$(date -u)"
echo "#define VERSION \"Last updated ${datetime}\"" | tee version.h
cd ../../
# download php dependencies
alias php='/usr/php/56/bin/php'

/usr/php/56/bin/php -c /home/chezzoco/phpextra/php.ini composer.phar -d=src install
/usr/php/56/bin/php -c /home/chezzoco/phpextra/php.ini src/artisan cataclysm:rebuild Cataclysm-DDA-master

echo "--------------------------"
echo "You need to make sure the webserver can read/write to the storage path"
echo ": $STORAGE_PATH"

