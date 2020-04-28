#!/bin/zsh

local -a whitelist=(
  "AMMO" "GUN" "ARMOR" "TOOL" "TOOL_ARMOR" "BOOK" "COMESTIBLE"
  "CONTAINER" "GUNMOD" "GENERIC" "BIONIC_ITEM" "VAR_VEH_PART"
  "MAGAZINE" "WHEEL" "TOOLMOD" "ENGINE" "VEHICLE_PART"
  "PET_ARMOR"
)

JQ="
  .[]
  | select(.id != null)
  | select(.type | test(\"${(j:|:)whitelist}\"; \"i\"))
  | .id
"

# {id:.id,type:.type}

function diff_ids() {
  mv new_id.txt old_id.txt
  jq -r $JQ $dir/data/**/*.json > new_id.txt
  local -a new=($(<new_id.txt)) old=($(<old_id.txt))
  print -l ${new:|old} >> src/public/latest.item.txt
  tail -n 200 src/public/latest.item.txt | sponge src/public/latest.item.txt
}

cd $0:A:h

if [[ -d ./Cataclysm-DDA/.git ]]; then
  dir=Cataclysm-DDA
  git --git-dir ./$dir fetch origin --depth 1
  git merge origin/master
elif [[ -d ./Cataclysm-DDA-master ]]; then
  dir=Cataclysm-DDA-master
  mv $dir $dir.bak
  wget https://github.wuyanzheshui.workers.dev/CleverRaven/Cataclysm-DDA/archive/master.zip
  unzip master.zip
fi

echo "#define VERSION \"$(date -u +'%Y-%m-%dT%H:%M:%SZ')\"" > $dir/src/version.h
msgfmt $dir/lang/po/zh_CN.po -o locale/zh_CN/LC_MESSAGES/cataclysm-dda.mo
diff_ids

php src/artisan down
sudo -u www-data php src/artisan cache:clear
sudo -u www-data php src/artisan cataclysm:rebuild $dir
php src/artisan up

# https://juejin.im/entry/5901af2e1b69e60058be2134

# cp locale/zh_CN/LC_MESSAGES/cataclysm-dda.mo /usr/share/locale/zh_CN/LC_MESSAGES/
