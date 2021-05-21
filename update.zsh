#!/bin/zsh

ROOT=$0:A:h

TRAPZERR() {
  cd $ROOT
  LOG "Recovering..."
  mv $dir.bak $dir
  php src/artisan down
  sudo -u www-data php -c ./php.ini src/artisan cataclysm:rebuild $dir
  php src/artisan up
  exit
}

LOG() {
  echo "[LOG][$(env TZ='Asia/Shanghai' date +'%Y-%m-%d %H:%M:%S')] $1"
}

cd $ROOT

LOG "Downloading latest source code..."
rm -f master.zip
curl -LOs https://github.wuyanzheshui.workers.dev/CleverRaven/Cataclysm-DDA/archive/master.zip || return

LOG "Unzipping..."
dir=Cataclysm-DDA-master
if [[ -d $dir ]]; then
  [[ ! -d $dir.bak ]] || rm -rdf $dir.bak
  mv -f $dir $dir.bak
fi
unzip -qo master.zip

echo "#define VERSION \"$(env TZ='Asia/Shanghai' date +'%Y-%m-%d %H:%M:%S')\"" > $dir/src/version.h

LOG "Transalting..."
pushd $dir
./lang/compile_mo.sh zh_CN
python3 ../translate_json_strings.py
popd

LOG "Generating diff..."
cp -f src/public/diff.json{,.bak}
python3 get_diff.py $dir.bak $dir src/public/diff.json

LOG "Building database..."
php src/artisan down
# sudo -u www-data php -c ./php.ini src/artisan cache:clear
sudo -u www-data php -c ./php.ini src/artisan cataclysm:rebuild $dir
php src/artisan up

LOG "Generating doc..."
cp doxygen_conf.txt $dir/doxygen_doc/doxygen_conf.txt
pushd $dir
doxygen doxygen_doc/doxygen_conf.txt
popd
doxyindexer $dir/doxygen_doc/searchdata.xml -o src/public/cgi-bin/
if [[ -d src/public/doc ]]; then
  rm -rdf src/public/doc
fi
mv $dir/doxygen_doc/html src/public/doc

LOG "Building cache..."
curl -sL "https://cdda-trunk.aloxaf.cn/search?q=丧尸浩克" > /dev/null || return

# https://juejin.im/entry/5901af2e1b69e60058be2134

# cp locale/zh_CN/LC_MESSAGES/cataclysm-dda.mo /usr/share/locale/zh_CN/LC_MESSAGES/
