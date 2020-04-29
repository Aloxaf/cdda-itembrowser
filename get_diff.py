#!/usr/bin/env python3
import json
import gettext
from pathlib import Path
from sys import argv
from typing import Dict, List, Union, Tuple

Json = Union[List["Json"], Dict[str, "Json"], str, bool, int, float]

WHITELIST_KEY = ["id", "name", "type", "ident"]
WHITELIST_TYPE = [
    "AMMO",
    "GUN",
    "ARMOR",
    "TOOL",
    "TOOL_ARMOR",
    "BOOK",
    "COMESTIBLE",
    "CONTAINER",
    "GUNMOD",
    "GENERIC",
    "BIONIC_ITEM",
    "VAR_VEH_PART",
    "_SPECIAL",
    "MAGAZINE",
    "WHEEL",
    "TOOLMOD",
    "ENGINE",
    "VEHICLE_PART",
    "PET_ARMOR",
    "MONSTER",
    "MATERIAL",
]

TRANS = gettext.translation("cataclysm-dda", localedir="locale", languages=["zh_CN"])
TRANS.install()


def parse_name(name: Json) -> Tuple[bool, str]:
    if isinstance(name, dict):
        if name.get("str"):
            s = name["str"]
        elif name.get("str_sp"):
            s = name["str_sp"]
        elif name.get("ctxt"):
            try:
                trans = TRANS.pgettext(name["ctxt"], name["str"])
                return (trans != name["str"], trans)
            except AttributeError:
                trans = TRANS.gettext(f"{name['ctxt']}\004{name['str']}")
                return (trans != name["str"] and "\004" not in trans, trans)
        elif isinstance(name, list):
            s = name[0]
        else:
            raise Exception(f"WTF: {name}")
    else:
        s = name

    trans = TRANS.ngettext(s, s, n=1)
    if trans == s:
        trans = TRANS.gettext(s)
    return (trans != s, trans)


def load_all_json(root: Path) -> Dict[str, Json]:
    data_dir = root / "data"
    ret: Dict[str, Json] = {}
    for file in data_dir.glob("**/*.json"):
        print(f"\rParsing {file}", end="")
        json_data = json.load(file.open("r", encoding="utf-8"))
        if not (isinstance(json_data, list) and isinstance(json_data[0], dict)):
            continue
        for entry in json_data:
            if entry.get("type", "").upper() not in WHITELIST_TYPE:
                continue
            eid = entry.get("id") or entry.get("ident")
            ret[eid] = {key: entry.get(key) for key in WHITELIST_KEY}
    return ret


if __name__ == "__main__":
    if len(argv) != 4:
        print(f"{argv[0]} OLD_CDDA_DIR NEW_CDDA_DIR DIFF.json")
        exit(0)

    old_path = Path(argv[1])
    new_path = Path(argv[2])
    if not old_path.exists() or not new_path.exists():
        print(f"Path not exists")
        exit(1)

    new = load_all_json(new_path)
    old = load_all_json(old_path)

    new_ids = set(new.keys())
    old_ids = set(old.keys())

    diff_add = new_ids - old_ids
    diff_del = old_ids - new_ids

    obj_add = [new[i] for i in diff_add]
    obj_del = [old[i] for i in diff_del]
    for i in obj_add:
        i["op"] = "add"
    for i in obj_del:
        i["op"] = "del"

    target = Path(argv[3])
    diff = [*obj_add, *obj_del]
    if target.exists():
        tmp = json.load(target.open("r", encoding="utf-8"))
        diff.extend(tmp[:400])

    for entry in diff:
        name = entry.get("name")
        if not isinstance(name, str) or name.isascii():
            if entry.get("raw_name"):
                (succ, trans) = parse_name(entry["raw_name"])
            else:
                (succ, trans) = parse_name(name)
            entry["name"] = trans
            if not succ and not entry.get("raw_name"):
                entry["raw_name"] = name
            elif succ and entry.get("raw_name"):
                del entry["raw_name"]

    json.dump(diff, open(argv[3], "w", encoding="utf-8"), indent=2, ensure_ascii=False)
