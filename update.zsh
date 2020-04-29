#!/bin/zsh

setopt errexit

cd $0:A:h

if [[ -d ./Cataclysm-DDA/.git ]]; then
  dir=Cataclysm-DDA
  git --git-dir ./$dir fetch origin --depth 1
  git merge origin/master
elif [[ -d ./Cataclysm-DDA-master ]]; then
  dir=Cataclysm-DDA-master
  rm -f master.zip
  rm -rdf $dir.bak
  mv -f $dir $dir.bak
  wget https://github.wuyanzheshui.workers.dev/CleverRaven/Cataclysm-DDA/archive/master.zip
  unzip master.zip
fi

echo "#define VERSION \"$(date -u +'%Y-%m-%dT%H:%M:%SZ')\"" > $dir/src/version.h
msgfmt $dir/lang/po/zh_CN.po -o locale/zh_CN/LC_MESSAGES/cataclysm-dda.mo
python3 get_diff.py Cataclysm-DDA-master.bak Cataclysm-DDA-master src/public/diff.json

php src/artisan down
sudo -u www-data php src/artisan cache:clear
sudo -u www-data php src/artisan cataclysm:rebuild $dir
php src/artisan up

# https://juejin.im/entry/5901af2e1b69e60058be2134

# cp locale/zh_CN/LC_MESSAGES/cataclysm-dda.mo /usr/share/locale/zh_CN/LC_MESSAGES/
