#!/bin/zsh

ROOT=$0:A:h

TRAPZERR() {
  cd $ROOT
  LOG "Recovering..."
  rm -rd $dir
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
rm -f 0.F-dev.zip
curl -LOs https://github.com.cnpmjs.org/CleverRaven/Cataclysm-DDA/archive/refs/heads/0.F-dev.zip || return

LOG "Unzipping..."
dir=Cataclysm-DDA-0.F-dev
if [[ -d $dir ]]; then
  [[ ! -d $dir.bak ]] || rm -rdf $dir.bak
  mv -f $dir $dir.bak
fi
unzip -qo 0.F-dev.zip

echo "#define VERSION \"0.F-dev + KeanMod: $(env TZ='Asia/Shanghai' date +'%Y-%m-%d %H:%M:%S')\"" > $dir/src/version.h

LOG "Downloading latest Mods..."
rm -f Kenan-Modpack-Mod.zip
rm -rdf Kenan-Modpack-Chinese
curl -LOs https://github.wuyanzheshui.workers.dev/linonetwo/CDDA-Kenan-Modpack-Chinese/releases/download/latest/Kenan-Modpack-Mod.zip
unzip -qo Kenan-Modpack-Mod.zip
cp -R Kenan-Modpack-Chinese/* $dir/data/mods

LOG "Transalting..."
pushd $dir
bash ./lang/compile_mo.sh zh_CN
python3 ../translate_json_strings.py
popd

LOG "Building database..."
php src/artisan down
# FOR dev: ln -s /tmp ./src/storage/framework/cache/data
# sudo -u www-data php -c ./php.ini src/artisan cache:clear
sudo -u www-data php -c ./php.ini src/artisan cataclysm:rebuild $dir
php src/artisan up

LOG "Building cache..."
curl -sL "http://127.0.0.1/search?q=丧尸浩克" > /dev/null || return
# https://juejin.im/entry/5901af2e1b69e60058be2134

# cp locale/zh_CN/LC_MESSAGES/cataclysm-dda.mo /usr/share/locale/zh_CN/LC_MESSAGES/
