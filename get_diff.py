#!/usr/bin/env python3
import json
import gettext
from pathlib import Path
from sys import argv
from typing import Dict, List, Union

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


def parse_name(name: Json) -> str:
    if isinstance(name, dict):
        if name.get("str_pl"):
            return TRANS.ngettext(name["str"], name["str_pl"], n=1)
        elif name.get("str_sp"):
            return TRANS.ngettext(name["str_sp"], name["str_sp"], n=1)
        elif name.get("ctxt"):
            try:
                return TRANS.pgettext(name["ctxt"], name["str"])
            except AttributeError:
                return TRANS.gettext(f"{name['ctxt']}\004{name['str']}")
        elif isinstance(name, list):
            return TRANS.ngettext(name[0], name[1], n=1)
        else:
            return TRANS.ngettext(name["str"], f"{name['str']}s", n=1)
    else:
        return TRANS.ngettext(name, f"{name}s", n=1)


def load_all_json(root: Path) -> Dict[str, Json]:
    data_dir = root / "data"
    ret: Dict[str, Json] = {}
    for file in data_dir.glob("**/*.json"):
        print(f"\rParsing {file}", end="")
        json_data = json.load(file.open('r', encoding='utf-8'))
        if not (isinstance(json_data, list) and isinstance(json_data[0], dict)):
            continue
        for entry in json_data:
            if entry.get("type", "").upper() not in WHITELIST_TYPE:
                continue
            eid = entry.get("id") or entry.get("ident")
            entry["name"] = parse_name(entry.get("name"))
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
        tmp = json.load(target.open('r', encoding='utf-8'))
        diff.extend([i for i in tmp if i["op"] == "add"][:100])
        diff.extend([i for i in tmp if i["op"] == "del"][:100])
    json.dump(diff, open(argv[3], "w", encoding='utf-8'), indent=2, ensure_ascii=False)
