#!/usr/bin/env python3
# 原地翻译 data/json 里的文件
# 建议翻译完用 make style-all-json 调整一下格式

import gettext
import json
import os
import itertools
import subprocess
from optparse import OptionParser
from sys import platform
from sys import exit

zh_CN = gettext.translation("cataclysm-dda", localedir="lang/mo", languages=["zh_CN"])
zh_CN.install()

# Must parse command line arguments here
# 'options' variable is referenced in our defined functions below

parser = OptionParser()
parser.add_option("-v", "--verbose", dest="verbose", help="be verbose")
(options, args) = parser.parse_args()


# Exceptions
class WrongJSONItem(Exception):
    def __init__(self, msg, item):
        self.msg = msg
        self.item = item

    def __str__(self):
        return "---\nJSON error\n{0}\n--- JSON Item:\n{1}\n---".format(
            self.msg, self.item)


# there may be some non-json files in data/raw
not_json = {os.path.normpath(i) for i in {
    "sokoban.txt",
    "LOADING_ORDER.md"
}}

# no warning will be given if an untranslatable object is found in those files
warning_suppressed_list = {os.path.normpath(i) for i in {
    "data/json/flags.json",
    "data/json/npcs/npc.json",
    "data/json/overmap_terrain.json",
    "data/json/statistics.json",
    "data/json/traps.json",
    "data/json/vehicleparts/",
    "data/raw/keybindings.json",
    "data/mods/alt_map_key/overmap_terrain.json",
    "data/mods/DeoxyMod/Deoxy_vehicle_parts.json",
    "data/mods/More_Survival_Tools/start_locations.json",
    "data/mods/NPC_Traits/npc_classes.json",
    "data/mods/Tanks/monsters.json",
}}


def warning_supressed(filename):
    for i in warning_suppressed_list:
        if filename.startswith(i):
            return True
    return False


# these files will not be parsed. Full related path.
ignore_files = {os.path.normpath(i) for i in {
    "data/json/anatomy.json",
    "data/json/items/book/abstract.json",
    "data/mods/replacements.json",
    "data/raw/color_templates/no_bright_background.json"
}}

# ignore these directories and their subdirectories
ignore_directories = {os.path.normpath(dir) for dir in {
    "data/mods/TEST_DATA",
}}

# these objects have no translatable strings
ignorable = {
    "ascii_art",
    "ammo_effect",
    "behavior",
    "butchery_requirement",
    "charge_removal_blacklist",
    "city_building",
    "colordef",
    "disease_type",
    "emit",
    "enchantment",
    "event_transformation",
    "EXTERNAL_OPTION",
    "hit_range",
    "ITEM_BLACKLIST",
    "item_group",
    "MIGRATION",
    "mod_tileset",
    "monster_adjustment",
    "MONSTER_BLACKLIST",
    "MONSTER_FACTION",
    "monstergroup",
    "MONSTER_WHITELIST",
    "mutation_type",
    "obsolete_terrain",
    "overlay_order",
    "overmap_connection",
    "overmap_location",
    "overmap_special",
    "profession_item_substitutions",
    "region_overlay",
    "region_settings",
    "relic_procgen_data",
    "requirement",
    "rotatable_symbol",
    "SCENARIO_BLACKLIST",
    "scent_type",
    "score",
    "skill_boost",
    "TRAIT_BLACKLIST",
    "trait_group",
    "uncraft",
    "vehicle_group",
    "vehicle_placement",
}

# these objects can have their strings automatically extracted.
# insert object "type" here IF AND ONLY IF
# all of their translatable strings are in the following form:
#   "name" member
#   "description" member
#   "text" member
#   "sound" member
#   "messages" member containing an array of translatable strings
automatically_convertible = {
    "activity_type",
    "AMMO",
    "ammunition_type",
    "ARMOR",
    "BATTERY",
    "bionic",
    "BIONIC_ITEM",
    "BOOK",
    "COMESTIBLE",
    "construction_category",
    "construction_group",
    "dream",
    "ENGINE",
    "event_statistic",
    "faction",
    "furniture",
    "GENERIC",
    "item_action",
    "ITEM_CATEGORY",
    "json_flag",
    "keybinding",
    "LOOT_ZONE",
    "MAGAZINE",
    "map_extra",
    "MOD_INFO",
    "MONSTER",
    "morale_type",
    "npc",
    "proficiency",
    "npc_class",
    "overmap_land_use_code",
    "overmap_terrain",
    "PET_ARMOR",
    "skill",
    "SPECIES",
    "speech",
    "SPELL",
    "start_location",
    "terrain",
    "TOOL",
    "TOOLMOD",
    "TOOL_ARMOR",
    "tool_quality",
    "vehicle",
    "vehicle_part",
    "vitamin",
    "WHEEL",
    "help",
    "weather_type"
}

# for these objects a plural form is needed
# NOTE: please also change `needs_plural` in `src/item_factory.cpp`
# when changing this list
needs_plural = {
    "AMMO",
    "ARMOR",
    "BATTERY",
    "BIONIC_ITEM",
    "BOOK",
    "COMESTIBLE",
    "ENGINE",
    "GENERIC",
    "GUN",
    "GUNMOD",
    "MAGAZINE",
    "MONSTER",
    "PET_ARMOR",
    "TOOL",
    "TOOLMOD",
    "TOOL_ARMOR",
    "WHEEL",
}

# These objects use a plural form in their description
needs_plural_desc = {
    "event_statistic"
}

# these objects can be automatically converted, but use format strings
use_format_strings = {
    "technique",
}

# For handling grammatical gender
all_genders = ["f", "m", "n"]


def gender_options(subject):
    return [subject + ":" + g for g in all_genders]

#
#  SPECIALIZED EXTRACTION FUNCTIONS
#


def extract_achievement(a):
    outfile = get_outfile(a["type"])
    for f in ("name", "description"):
        if f in a:
            a[f] = writestr(a[f])
    for req in a.get("requirements", ()):
        if "description" in req:
            req["description"] = writestr(req["description"])


def extract_bodypart(item):
    outfile = get_outfile("bodypart")
    # See comments in `body_part_struct::load` of bodypart.cpp about why xxx
    # and xxx_multiple are not inside a single translation object.
    item["name"] = writestr(item["name"])
    if "name_multiple" in item:
        item["name_multiple"] = writestr(item["name_multiple"])
    item["accusative"] = writestr(item["accusative"])
    if "accusative_multiple" in item:
        item["accusative_multiple"] = writestr(item["accusative_multiple"])
    item["encumbrance_text"] = writestr(item["encumbrance_text"])
    item["heading"] = writestr(item["heading"])
    item["heading_multiple"] = writestr(item["heading_multiple"])
    if "smash_message" in item:
        item["smash_message"] = writestr(item["smash_message"])
    if "hp_bar_ui_text" in item:
        item["hp_bar_ui_text"] = writestr(item["hp_bar_ui_text"])


def extract_clothing_mod(item):
    outfile = get_outfile("clothing_mod")
    item["implement_prompt"] = writestr(item["implement_prompt"])
    item["destroy_prompt"] = writestr(item["destroy_prompt"])


def extract_construction(item):
    outfile = get_outfile("construction")
    if "pre_note" in item:
        item["pre_note"] = writestr(item["pre_note"])


def extract_harvest(item):
    outfile = get_outfile("harvest")
    if "message" in item:
        item["message"] = writestr(item["message"])


def extract_material(item):
    outfile = get_outfile("material")
    item["name"] = writestr(item["name"])
    if "bash_dmg_verb" in item:
        item["bash_dmg_verb"] = writestr(item["bash_dmg_verb"])
    if "cut_dmg_verb" in item:
        item["cut_dmg_verb"] = writestr(item["cut_dmg_verb"])
    if "dmg_adj" in item:
        item["dmg_adj"][0] = writestr(item["dmg_adj"][0])
        item["dmg_adj"][1] = writestr(item["dmg_adj"][1])
        item["dmg_adj"][2] = writestr(item["dmg_adj"][2])
        item["dmg_adj"][3] = writestr(item["dmg_adj"][3])



def extract_martial_art(item):
    outfile = get_outfile("martial_art")
    if "name" in item:
        item["name"] = writestr(item["name"])
    else:
        name = item["id"]
    if "description" in item:
        item["description"] = writestr(item["description"])
    if "initiate" in item:
        item["initiate"] = writestr(item["initiate"])
    onhit_buffs = item.get("onhit_buffs", list())
    static_buffs = item.get("static_buffs", list())
    onmove_buffs = item.get("onmove_buffs", list())
    ondodge_buffs = item.get("ondodge_buffs", list())
    onattack_buffs = item.get("onattack_buffs", list())
    onpause_buffs = item.get("onpause_buffs", list())
    onblock_buffs = item.get("onblock_buffs", list())
    ongethit_buffs = item.get("ongethit_buffs", list())
    onmiss_buffs = item.get("onmiss_buffs", list())
    oncrit_buffs = item.get("oncrit_buffs", list())
    onkill_buffs = item.get("onkill_buffs", list())

    buffs = (onhit_buffs + static_buffs + onmove_buffs + ondodge_buffs +
             onattack_buffs + onpause_buffs + onblock_buffs + ongethit_buffs +
             onmiss_buffs + oncrit_buffs + onkill_buffs)
    for buff in buffs:
        buff["name"] = writestr(buff["name"])
        buff["description"] = writestr(buff["description"])


def extract_move_mode(item):
    outfile = get_outfile("move_modes")
    # Move mode name
    item["name"] = writestr(item["name"])
    # The character in the move menu
    item["character"] = writestr(item["character"])
    # The character in the panels
    item["panel_char"] = writestr(item["panel_char"])
    # Successful change message
    item["change_good_none"] = writestr(item["change_good_none"])
    # Successful change message (animal steed)
    item["change_good_animal"] = writestr(item["change_good_animal"])
    # Successful change message (mech steed)
    item["change_good_mech"] = writestr(item["change_good_mech"])
    if "change_bad_none" in item:
        # Failed change message
        item["change_bad_none"] = writestr(item["change_bad_none"])
    if "change_bad_animal" in item:
        # Failed change message (animal steed)
        item["change_bad_animal"] = writestr(item["change_bad_animal"])
    if "change_bad_mech" in item:
        # Failed change message (mech steed)
        item["change_bad_mech"] = writestr(item["change_bad_mech"])


def extract_effect_type(item):
    # writestr will not write string if it is None.
    outfile = get_outfile("effects")
    ctxt_name = item.get("name", ())

    if item.get("name"):
        item["name"] = [writestr(i) for i in item["name"]]
    if item.get("desc"):
        item["desc"] = [writestr(i) for i in item["desc"]]
    if item.get("reduced_desc"):
        item["reduced_desc"] = [writestr(i) for i in item["reduced_desc"]]

    keys = ["apply_message", "remove_message", "miss_messages", "decay_messages"]
    for key in keys:
        if item.get(key):
            item[key] = writestr(item[key])

    # speed_name
    if "speed_name" in item:
        item["speed_name"] = writestr(item["speed_name"])
    if item.get("apply_memorial_log"):
        item["apply_memorial_log"] = writestr(item["apply_memorial_log"], context="memorial_male")
    if item.get("remove_memorial_log"):
        item["remove_memorial_log"] = writestr(item["remove_memorial_log"], context="memorial_male")

def extract_gun(item):
    outfile = get_outfile("gun")
    if "name" in item:
        if item["type"] in needs_plural:
            item["name"] = writestr(item["name"], pl_fmt=True)
        else:
            item["name"] = writestr(item["name"])
    if "description" in item:
        item["description"] = writestr(item["description"])
    if "modes" in item:
        modes = item.get("modes")
        for fire_mode in modes:
            fire_mode[1] = writestr(fire_mode[1])
    if "skill" in item:
        # legacy code: the "gun type" is calculated in `item::gun_type` and
        # it's basically the skill id, except for archery (which is either
        # bow or crossbow). Once "gun type" is loaded from JSON, it should
        # be extracted directly.
        if not item["skill"] == "archery":
            item["skill"] = writestr(item["skill"], context="gun_type_type")
        else:
            item["skill"] = writestr("bow", context="gun_type_type")
    if "reload_noise" in item:
        item["reload_noise"] = writestr(item["reload_noise"])
    if "use_action" in item:
        use_action = item.get("use_action")
        item_name = item.get("name")
        extract_use_action_msgs(outfile, use_action, item_name, {})
    if "valid_mod_locations" in item:
        for mod_loc in item["valid_mod_locations"]:
            mod_loc[0] = writestr(mod_loc[0])
    if isinstance(item.get("ranged_damage"), dict) and item["ranged_damage"].get("damage_type"):
        damage_type = item["ranged_damage"]["damage_type"]
        if damage_type == "bullet":
            item["ranged_damage"]["damage_type"] = writestr(damage_type, context="damage_type")
        else:
            item["ranged_damage"]["damage_type"] = writestr(damage_type, context="damage type")


def extract_gunmod(item):
    outfile = get_outfile("gunmod")
    if "name" in item:
        if item["type"] in needs_plural:
            item["name"] = writestr(item["name"], pl_fmt=True)
        else:
            item["name"] = writestr(item["name"])
    if "description" in item:
        item["description"] = writestr(item["description"])
    if "mode_modifier" in item:
        modes = item.get("mode_modifier")
        for fire_mode in modes:
            fire_mode[1] = writestr(fire_mode[1])
    if "location" in item:
        item["location"] = writestr(item["location"])
    if "mod_targets" in item:
        item["mod_targets"] = [writestr(target, context="gun_type_type") for target in item["mod_targets"]]


def extract_professions(item):
    outfile = get_outfile("professions")
    nm = item.get("name")
    if type(nm) == dict:
        nm["male"] = writestr(nm["male"], context="profession_male")
        item["description"] = writestr(item["description"], context="prof_desc_male")

        nm["female"] = writestr(nm["female"], context="profession_female")
        item["description"] = writestr(item["description"], context="prof_desc_female")
    else if nm is not None:
        item["name"] = writestr(nm, context="profession_male")
        item["description"] = writestr(item["description"], context="prof_desc_male")

        item["name"] = writestr(nm, context="profession_female")
        item["description"] = writestr(item["description"], context="prof_desc_female")
    return item


def extract_scenarios(item):
    outfile = get_outfile("scenario")
    # writestr will not write string if it is None.
    if item.get("name"):
        item["name"] = writestr(item["name"], context="scenario_male")
        msg = item.get("description")
        if msg:
            item["description"] = writestr(msg, context="scen_desc_male")
        msg = item.get("start_name")
        if msg:
            item["start_name"] = writestr(msg, context="start_name")
    else:
        for f in ["description", "start_name"]:
            found = item.get(f, None)
            if found:
                item[f] = writestr(found)


def items_sorted_by_key(d):
    return sorted(d.items(), key=lambda x: x[0])


def extract_mapgen(item):
    outfile = get_outfile("mapgen")
    # writestr will not write string if it is None.
    for (objkey, objval) in items_sorted_by_key(item["object"]):
        if objkey == "place_specials" or objkey == "place_signs":
            for special in objval:
                for (speckey, specval) in items_sorted_by_key(special):
                    if speckey == "signage":
                        special[speckey] = writestr(specval)
        elif objkey == "signs":
            for (k, v) in items_sorted_by_key(objval):
                sign = v.get("signage", None)
                v["signage"] = writestr(sign)
        elif objkey == "computers":
            for (k, v) in items_sorted_by_key(objval):
                if "name" in v:
                    v["name"] = writestr(v.get("name"))
                if "options" in v:
                    for opt in v.get("options"):
                        opt["name"] = writestr(opt.get("name"))
                if "access_denied" in v:
                    v["access_denied"] = writestr(v.get("access_denied"))


def extract_palette(item):
    outfile = get_outfile("palette")
    if "signs" in item:
        for (k, v) in items_sorted_by_key(item["signs"]):
            if v.get("signage"):
                v["signage"] = writestr(v["signage"], comment="Sign")


def extract_monster_attack(item):
    outfile = get_outfile("monster_attack")
    if "hit_dmg_u" in item:
        item["hit_dmg_u"] = writestr(item.get("hit_dmg_u"))
    if "hit_dmg_npc" in item:
        item["hit_dmg_npc"] = writestr(item.get("hit_dmg_npc"))
    if "no_dmg_msg_u" in item:
        item["no_dmg_msg_u"] = writestr(item.get("no_dmg_msg_u"))
    if "no_dmg_msg_npc" in item:
        item["no_dmg_msg_npc"] = writestr(item.get("no_dmg_msg_npc"))


def extract_recipes(item):
    outfile = get_outfile("recipe")
    if "book_learn" in item:
        if type(item["book_learn"]) is dict:
            for (k, v) in item["book_learn"].items():
                if type(v) is dict and "recipe_name" in v:
                    v["recipe_name"] = writestr(v["recipe_name"])
    if "description" in item:
        item["description"] = writestr(item["description"])
    if "blueprint_name" in item:
        item["blueprint_name"] = writestr(item["blueprint_name"])


def extract_recipe_group(item):
    outfile = get_outfile("recipe_group")
    if "recipes" in item:
        for i in item.get("recipes"):
            i["description"] = writestr(i.get("description"))


def extract_gendered_dynamic_line_optional(line, outfile):
    if "gendered_line" in line:
        msg = line["gendered_line"]
        subjects = line["relevant_genders"]
        options = [gender_options(subject) for subject in subjects]
        for context_list in itertools.product(*options):
            context = " ".join(context_list)
            line["gendered_line"] = writestr(msg, context=context)


def extract_dynamic_line_optional(line, member, outfile):
    if member in line:
        extract_dynamic_line(line[member], outfile)


dynamic_line_string_keys = [
    # from `simple_string_conds` in `condition.h`
    "u_male", "u_female", "npc_male", "npc_female",
    "has_no_assigned_mission", "has_assigned_mission",
    "has_many_assigned_missions", "has_no_available_mission",
    "has_available_mission", "has_many_available_missions",
    "mission_complete", "mission_incomplete", "mission_has_generic_rewards",
    "npc_available", "npc_following", "npc_friend", "npc_hostile",
    "npc_train_skills", "npc_train_styles",
    "at_safe_space", "is_day", "npc_has_activity", "is_outside", "u_has_camp",
    "u_can_stow_weapon", "npc_can_stow_weapon", "u_has_weapon",
    "npc_has_weapon", "u_driving", "npc_driving",
    "has_pickup_list", "is_by_radio", "has_reason",
    # yes/no strings for complex conditions, 'and' list
    "yes", "no", "and"
]


def extract_dynamic_line(line, outfile):
    # TODO:
    if type(line) == list:
        for l in line:
            extract_dynamic_line(l, outfile)
    elif type(line) == dict:
        extract_gendered_dynamic_line_optional(line, outfile)
        for key in dynamic_line_string_keys:
            extract_dynamic_line_optional(line, key, outfile)
    elif type(line) == str:
        writestr(line)


def extract_talk_effects(effects, outfile):
    if type(effects) != list:
        effects = [effects]
    for eff in effects:
        if type(eff) == dict:
            if "u_buy_monster" in eff and "name" in eff:
                eff["name"] = writestr(eff["name"])


def extract_talk_response(response, outfile):
    if "text" in response:
        response["text"] = writestr(response["text"])
    if "truefalsetext" in response:
        response["truefalsetext"]["true"] = writestr(response["truefalsetext"]["true"])
        response["truefalsetext"]["false"] = writestr(response["truefalsetext"]["false"])
    if "success" in response:
        extract_talk_response(response["success"], outfile)
    if "failure" in response:
        extract_talk_response(response["failure"], outfile)
    if "speaker_effect" in response:
        speaker_effects = response["speaker_effect"]
        if type(speaker_effects) != list:
            speaker_effects = [speaker_effects]
        for eff in speaker_effects:
            if "effect" in eff:
                extract_talk_effects(eff["effect"], outfile)
    if "effect" in response:
        extract_talk_effects(response["effect"], outfile)


def extract_talk_topic(item):
    outfile = get_outfile("talk_topic")
    if "dynamic_line" in item:
        extract_dynamic_line(item["dynamic_line"], outfile)
    if "responses" in item:
        for r in item["responses"]:
            extract_talk_response(r, outfile)
    if "repeat_responses" in item:
        rr = item["repeat_responses"]
        if type(rr) is dict and "response" in rr:
            extract_talk_response(rr["response"], outfile)
        elif type(rr) is list:
            for r in rr:
                if "response" in r:
                    extract_talk_response(r["response"], outfile)
    if "effect" in item:
        extract_talk_effects(item["effect"], outfile)


def extract_trap(item):
    outfile = get_outfile("trap")
    item["name"] = writestr(item["name"])
    if "vehicle_data" in item and "sound" in item["vehicle_data"]:
        item["vehicle_data"]["sound"] = writestr(item["vehicle_data"]["sound"])


def extract_missiondef(item):
    outfile = get_outfile("mission_def")
    item_name = item.get("name")
    if item_name is None:
        raise WrongJSONItem("JSON item don't contain 'name' field", item)
    item["name"] = writestr(item_name)
    if "description" in item:
        item["description"] = writestr(item["description"])
    if "dialogue" in item:
        dialogue = item.get("dialogue")
        for k in ["describe", "offer", "accepted", "rejected", "advice", "inquire", "success", "success_lie", "failure"]:
            if dialogue.get(k):
                dialogue[k] = writestr(dialogue[k])
    if "start" in item and "effect" in item["start"]:
        extract_talk_effects(item["start"]["effect"], outfile)
    if "end" in item and "effect" in item["end"]:
        extract_talk_effects(item["end"]["effect"], outfile)
    if "fail" in item and "effect" in item["fail"]:
        extract_talk_effects(item["fail"]["effect"], outfile)


def extract_mutation(item):
    outfile = get_outfile("mutation")

    item_name_or_id = found = item.get("name")
    if found is None:
        if "copy-from" in item:
            item_name_or_id = item["id"]
        else:
            raise WrongJSONItem("JSON item don't contain 'name' field", item)
    else:
        item["name"] = writestr(found)

    simple_fields = ["description"]

    for f in simple_fields:
        found = item.get(f)
        # Need that check due format string argument
        if found is not None:
            item[f] = writestr(found)

    if "attacks" in item:
        attacks = item.get("attacks")
        if type(attacks) is list:
            for i in attacks:
                if "attack_text_u" in i:
                    i["attack_text_u"] = writestr(i.get("attack_text_u"))
                if "attack_text_npc" in i:
                    i["attack_text_npc"] = writestr(i.get("attack_text_npc"))
        else:
            if "attack_text_u" in attacks:
                attacks["attack_text_u"] = writestr(attacks.get("attack_text_u"))
            if "attack_text_npc" in attacks:
                attacks["attack_text_npc"] = writestr(attacks.get("attack_text_npc"))

    if "spawn_item" in item:
        item["spawn_item"]["message"] = writestr(item.get("spawn_item").get("message"))

    if "ranged_mutation" in item:
        item["ranged_mutation"]["message"] = writestr(item.get("ranged_mutation").get("message"))


def extract_mutation_category(item):
    outfile = get_outfile("mutation_category")

    item_name = found = item.get("name")
    if found is None:
        raise WrongJSONItem("JSON item don't contain 'name' field", item)
    item["name"] = writestr(found)

    simple_fields = ["mutagen_message",
                     "iv_message",
                     "iv_sleep_message",
                     "iv_sound_message",
                     "junkie_message"
                     ]

    for f in simple_fields:
        found = item.get(f)
        # Need that check due format string argument
        if found is not None:
            item[f] = writestr(found)

    found = item.get("memorial_message")
    item["memorial_message"] = writestr(found, context="memorial_male")


def extract_vehspawn(item):
    outfile = get_outfile("vehicle_spawn")

    found = item.get("spawn_types")
    if not found:
        return

    for st in found:
        st["description"] = writestr(st.get("description"))


def extract_recipe_category(item):
    # TODO:
    outfile = get_outfile("recipe_category")

    cid = item.get("id", None)
    if cid:
        if cid == 'CC_NONCRAFT':
            return
        cat_name = cid.split("_")[1]
        writestr(cat_name, comment="Crafting recipes category name")
    else:
        raise WrongJSONItem("Recipe category must have unique id", item)

    found = item.get("recipe_subcategories", [])
    for subcat in found:
        if subcat == 'CSC_ALL':
            writestr('ALL',
                     comment="Crafting recipes subcategory all")
            continue
        subcat_name = subcat.split('_')[2]
        comment = "Crafting recipes subcategory of '{}' category".format(
            cat_name)
        writestr(subcat_name, comment=comment)


def extract_gate(item):
    outfile = get_outfile("gates")
    messages = item.get("messages", {})

    for (k, v) in sorted(messages.items(), key=lambda x: x[0]):
        messages[k] = writestr(v)


def extract_field_type(item):
    outfile = get_outfile("field_type")
    for fd in item.get("intensity_levels"):
        if "name" in fd:
            fd["name"] = writestr(fd.get("name"))


def extract_ter_furn_transform_messages(item):
    outfile = get_outfile("ter_furn_transform_messages")
    if "fail_message" in item:
        item["fail_message"] = writestr(item.get("fail_message"))
    if "terrain" in item:
        for terrain in item.get("terrain"):
            terrain["message"] = writestr(terrain.get("message"))
    if "furniture" in item:
        for furniture in item.get("furniture"):
            furniture["message"] = writestr(furniture.get("message"))


def extract_skill_display_type(item):
    outfile = get_outfile("skill_display_type")
    item["display_string"] = writestr(item["display_string"])


def extract_fault(item):
    outfile = get_outfile("fault")
    item["name"] = writestr(item["name"])
    item["description"] = writestr(item["description"])
    for method in item["mending_methods"]:
        if "name" in method:
            method["name"] = writestr(method["name"])
        if "description" in method:
            method["description"] = writestr(method["description"])
        if "success_msg" in method:
            method["success_msg"] = writestr(method["success_msg"], format_strings=True)


def extract_snippets(item):
    # TODO:
    outfile = get_outfile("snippet")
    text = item["text"]
    if type(text) is not list:
        text = [text]
    for snip in text:
        if type(snip) is str:
            writestr(snip)
        else:
            writestr(snip["text"])


def extract_vehicle_part_category(item):
    outfile = get_outfile("vehicle_part_categories")
    name = item.get("name")
    short_name = item.get("short_name")
    item["name"] = writestr(name)
    item["short_name"] = writestr(short_name)


# these objects need to have their strings specially extracted
extract_specials = {
    "achievement": extract_achievement,
    "body_part": extract_bodypart,
    "clothing_mod": extract_clothing_mod,
    "conduct": extract_achievement,
    "construction": extract_construction,
    "effect_type": extract_effect_type,
    "fault": extract_fault,
    "GUN": extract_gun,
    "GUNMOD": extract_gunmod,
    "harvest": extract_harvest,
    "mapgen": extract_mapgen,
    "martial_art": extract_martial_art,
    "material": extract_material,
    "mission_definition": extract_missiondef,
    "monster_attack": extract_monster_attack,
    "movement_mode": extract_move_mode,
    "mutation": extract_mutation,
    "mutation_category": extract_mutation_category,
    "palette": extract_palette,
    "profession": extract_professions,
    "recipe_category": extract_recipe_category,
    "recipe": extract_recipes,
    "recipe_group": extract_recipe_group,
    "scenario": extract_scenarios,
    "snippet": extract_snippets,
    "talk_topic": extract_talk_topic,
    "trap": extract_trap,
    "gate": extract_gate,
    "vehicle_spawn": extract_vehspawn,
    "field_type": extract_field_type,
    "ter_furn_transform": extract_ter_furn_transform_messages,
    "skill_display_type": extract_skill_display_type,
    "vehicle_part_category": extract_vehicle_part_category,
}

#
#  PREPARATION
#

directories = {os.path.normpath(i) for i in {
    "data/raw",
    "data/json",
    "data/mods",
    "data/core",
    "data/help",
}}
to_dir = os.path.normpath("lang/json")

print("==> Preparing the work space")

# allow running from main directory, or from script subdirectory
if not os.path.exists("data/json"):
    print("Error: Couldn't find the 'data/json' subdirectory.")
    exit(1)

# create the output directory, if it does not already exist
if not os.path.exists(to_dir):
    os.mkdir(to_dir)

# clean any old extracted strings, it will all be redone
for filename in os.listdir(to_dir):
    if not filename.endswith(".py"):
        continue
    f = os.path.join(to_dir, filename)
    os.remove(f)

#
#  FUNCTIONS
#


def tlcomment(fs, string):
    "Write the string to the file as a comment for translators."
    if len(string) > 0:
        for line in string.splitlines():
            fs.write("#~ {}\n".format(line))

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


# `context` is deprecated and only for use in legacy code. Use
# `class translation` to read the text in c++ and specify the context in json
# instead.
def writestr(string, context=None, pl_fmt=False, format_strings=False, comment=None):
    "Wrap the string and write to the file."
    if type(string) is list:
        return [writestr(entry, context, pl_fmt) for entry in string]
    elif type(string) is dict:
        if context is None:
            context = string.get("ctxt")
        elif "ctxt" in string:
            raise WrongJSONItem("ERROR: 'ctxt' found in json when `context` "
                                "parameter is specified", string)
        if "str_pl" in string:
            string["str_pl"] = npgettext(context, string["str_pl"], string["str_pl"])
        if "str" in string:
            string["str"] = npgettext(context, string["str"], pl_fmt)
        elif "str_sp" in string:
            string["str_sp"] = npgettext(context, string["str_sp"], string["str_sp"])
        else:
            raise WrongJSONItem("ERROR: 'str' or 'str_sp' not found", string)
    elif type(string) is str:
        string = npgettext(context, string, pl_fmt)
    elif string is None:
        return
    else:
        print("WARN: value is not a string, dict, list, or None", string)
    
    return string


def get_outfile(json_object_type):
    return os.path.join(to_dir, json_object_type + "_from_json.py")


use_action_msgs = {
    "activate_msg",
    "deactive_msg",
    "out_of_power_msg",
    "msg",
    "menu_text",
    "message",
    "friendly_msg",
    "hostile_msg",
    "need_fire_msg",
    "need_charges_msg",
    "non_interactive_msg",
    "unfold_msg",
    "sound_msg",
    "no_deactivate_msg",
    "not_ready_msg",
    "success_message",
    "lacks_fuel_message",
    "failure_message",
    "descriptions",
    "use_message",
    "noise_message",
    "bury_question",
    "done_message",
    "voluntary_extinguish_message",
    "charges_extinguish_message",
    "water_extinguish_message",
    "auto_extinguish_message",
    "activation_message",
    "holster_msg",
    "holster_prompt",
    "verb",
    "gerund"
}


def extract_use_action_msgs(outfile, use_action, it_name, kwargs):
    """Extract messages for iuse_actor objects. """
    for f in sorted(use_action_msgs):
        if type(use_action) is dict and f in use_action:
            if it_name:
                use_action[f] = writestr(use_action[f], **kwargs)
    # Recursively check sub objects as they may contain more messages.
    if type(use_action) is list:
        for i in use_action:
            extract_use_action_msgs(outfile, i, it_name, kwargs)
    elif type(use_action) is dict:
        for (k, v) in sorted(use_action.items(), key=lambda x: x[0]):
            extract_use_action_msgs(outfile, v, it_name, kwargs)


found_types = set()
known_types = (ignorable | use_format_strings | extract_specials.keys() |
               automatically_convertible)


# extract commonly translatable data from json to fake-python
def extract(item, infilename):
    """Find any extractable strings in the given json object,
    and write them to the appropriate file."""
    if "type" not in item:
        return
    object_type = item["type"]
    found_types.add(object_type)
    outfile = get_outfile(object_type)
    kwargs = {}
    if object_type in ignorable:
        return
    elif object_type in use_format_strings:
        kwargs["format_strings"] = True
    elif object_type in extract_specials:
        extract_specials[object_type](item)
        return
    elif object_type not in automatically_convertible:
        print(
            "ERROR: Unrecognized object type '{}'!".format(object_type), json.dumps(item))
    if object_type not in known_types:
        print("WARNING: known_types does not contain object type '{}'".format(
              object_type))
    wrote = False
    name = item.get("name")  # Used in gettext comments below.
    # Don't extract any record with name = "none".
    if name and name == "none":
        return
    if name:
        if object_type in needs_plural:
            item["name"] = writestr(name, pl_fmt=True, **kwargs)
        else:
            item["name"] = writestr(name, **kwargs)
        if type(name) is dict and "str" in name:
            singular_name = name["str"]
        else:
            singular_name = name

    def do_extract(item):
        if "name_suffix" in item:
            item["name_suffix"] = writestr(item["name_suffix"], **kwargs)
        if "name_unique" in item:
            item["name_unique"] = writestr(item["name_unique"], **kwargs)
        if "job_description" in item:
            item["job_description"] = writestr(item["job_description"], **kwargs)
        if "use_action" in item:
            extract_use_action_msgs(outfile, item["use_action"], singular_name, kwargs)
        if "conditional_names" in item:
            for cname in item["conditional_names"]:
                cname["name"] = writestr(cname["name"], pl_fmt=True, **kwargs)
        if "description" in item:
            if object_type in needs_plural_desc:
                item["description"] = writestr(item["description"], pl_fmt=True, **kwargs)
            else:
                item["description"] = writestr(item["description"], **kwargs)
        if "detailed_definition" in item:
            item["detailed_definition"] = writestr(item["detailed_definition"], **kwargs)
        if "sound" in item:
            item["sound"] = writestr(item["sound"], **kwargs)
        if "sound_description" in item:
            item["sound_description"] = writestr(item["sound_description"], **kwargs)
        if "snippet_category" in item and type(item["snippet_category"]) is list:
            # snippet_category is either a simple string (the category ident)
            # which is not translated, or an array of snippet texts.
            for entry in item["snippet_category"]:
                # Each entry is a json-object with an id and text
                if type(entry) is dict:
                    entry["text"] = writestr(entry["text"], **kwargs)
                else:
                    # or a simple string
                    # TODO: 
                    writestr(entry, **kwargs)
                    wrote = True
        if "bash" in item and type(item["bash"]) is dict:
            # entries of type technique have a bash member, too.
            # but it's a int, not an object.
            bash = item["bash"]
            if "sound" in bash:
                bash["sound"] = writestr(bash["sound"], **kwargs)
            if "sound_fail" in bash:
                bash["sound_fail"] = writestr(bash["sound_fail"], **kwargs)
        if "seed_data" in item:
            seed_data = item["seed_data"]
            seed_data["plant_name"] = writestr(seed_data["plant_name"], **kwargs)
        if "relic_data" in item and "name" in item["relic_data"]:
            item["relic_data"]["name"] = writestr(item["relic_data"]["name"], **kwargs)
        if "text" in item:
            item["text"] = writestr(item["text"], **kwargs)
        if "message" in item:
            item["message"] = writestr(item["message"], format_strings=True, **kwargs)
        if "messages" in item:
            item["messages"] = [writestr(message, **kwargs) for message in item["messages"]]
        if "valid_mod_locations" in item:
            for mod_loc in item["valid_mod_locations"]:
                mod_loc[0] = writestr(mod_loc[0], **kwargs)
        if "info" in item:
            item["info"] = writestr(item["info"], **kwargs)
        if "restriction" in item:
            item["restriction"] = writestr(item["restriction"], **kwargs)
        if "verb" in item:
            item["verb"] = writestr(item["verb"], **kwargs)
        if "special_attacks" in item:
            special_attacks = item["special_attacks"]
            for special_attack in special_attacks:
                if "description" in special_attack:
                    special_attack["description"] = writestr(special_attack["description"], **kwargs)
                if "monster_message" in special_attack:
                    special_attack["monster_message"] = writestr(special_attack["monster_message"], **kwargs)
                if "targeting_sound" in special_attack:
                    special_attack["targeting_sound"] = writestr(special_attack["targeting_sound"], **kwargs)
                if "no_ammo_sound" in special_attack:
                    special_attack["no_ammo_sound"] = writestr(outfile, special_attack["no_ammo_sound"], **kwargs)
        if "footsteps" in item:
            item["footsteps"] = writestr(item["footsteps"], **kwargs)
        if "revert_msg" in item:
            item["revert_msg"] = writestr(item["revert_msg"], **kwargs)
    do_extract(item)
    if "extend" in item:
        do_extract(item["extend"])


def extract_all_from_dir(json_dir):
    """Extract strings from every json file in the specified directory,
    recursing into any subdirectories."""
    allfiles = os.listdir(json_dir)
    allfiles.sort()
    dirs = []
    skiplist = [os.path.normpath(".gitkeep")]
    for f in allfiles:
        full_name = os.path.join(json_dir, f)
        if os.path.isdir(full_name):
            dirs.append(f)
        elif f in skiplist or full_name in ignore_files:
            continue
        elif any(full_name.startswith(dir) for dir in ignore_directories):
            continue
        elif f.endswith(".json"):
            extract_all_from_file(full_name)
        elif f not in not_json:
            if options.verbose:
                print("Skipping file: '{}'".format(f))
    for d in dirs:
        extract_all_from_dir(os.path.join(json_dir, d))


def extract_all_from_file(json_file):
    "Extract translatable strings from every object in the specified file."
    if options.verbose:
        print("Loading {}".format(json_file))

    with open(json_file, encoding="utf-8") as fp:
        jsondata = json.load(fp)
    # it's either an array of objects, or a single object
    try:
        if hasattr(jsondata, "keys"):
            extract(jsondata, json_file)
        else:
            for jsonobject in jsondata:
                extract(jsonobject, json_file)
        json.dump(jsondata, open(json_file, mode="w", encoding="utf-8"), ensure_ascii=False, indent=2)
    except WrongJSONItem as E:
        print("---\nFile: '{0}'".format(json_file))
        print(E)
        exit(1)

#
#  EXTRACTION
#

ignored_types = []

# first, make sure we aren't erroneously ignoring types
for ignored in ignorable:
    if ignored in automatically_convertible:
        ignored_types.append(ignored)
    if ignored in extract_specials:
        ignored_types.append(ignored)

if len(ignored_types) != 0:
    print("ERROR: Some types set to be both extracted and ignored:")
    for ignored in ignored_types:
        print(ignored)
    exit(-1)

print("==> Parsing JSON")
for i in sorted(directories):
    print("----> Traversing directory {}".format(i))
    extract_all_from_dir(i)
print("==> Finalizing")
if len(known_types - found_types) != 0:
    print("WARNING: type {} not found in any JSON objects".format(
        known_types - found_types))
if len(needs_plural - found_types) != 0:
    print("WARNING: type {} from needs_plural not found in any JSON "
          "objects".format(needs_plural - found_types))

print("Output files in %s" % to_dir)

# done.
