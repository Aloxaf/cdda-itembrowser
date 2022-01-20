#!/usr/bin/python

from pathlib import Path
import typing as t
import json
import gettext


Entry = t.Dict[str, t.Union[str, float, int, dict, list]]

zh_CN = gettext.translation("cataclysm-dda", localedir="lang/mo", languages=["zh_CN"])
zh_CN.install()
TRANS = {}


def npgettext(context, single, plural):
    # Fuck python 3.6, which doens't support pgettext
    if context:
        if not plural:
            text = zh_CN.gettext(f"{context}\004{single}")
        if plural or text == single:
            text = zh_CN.ngettext(f"{context}\004{single}", f"{context}\004{plural}", 1)
    else:
        if not single:
            return ''
        if not plural:
            text = zh_CN.gettext(single)
        if plural or text == single:
            text = zh_CN.ngettext(single, plural, 1)
    return single if '\004' in text else text


def extract(data: Entry):
    if isinstance(data, dict) and isinstance(data.get("id"), str):
        mid = data["id"]
        if isinstance(data.get("name"), str):
            name = data["name"]
        elif isinstance(data.get("name"), dict):
            name = data["name"]
            if name.get("str"):
                name = name["str"]
            elif name.get("str_sp"):
                name = name["str_sp"]
            else:
                return
        else:
            return
        TRANS[mid] = npgettext(None, name, None)


for file in Path("data").rglob("**/*.json"):
    data = json.load(file.open())
    if isinstance(data, list):
        for d in data:
            extract(d)
    else:
        extract(data)

print("return {")
for k in sorted(TRANS.keys()):
    v = TRANS[k]
    print(f'[{repr(k)}] = {repr(v)},')
print("}")
