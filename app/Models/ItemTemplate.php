<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemTemplate extends Model
{
    use HasFactory;

    protected $connection = 'mysql_world';
    protected $table = 'item_template';
    protected $primaryKey = 'entry';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'entry', 'class', 'subclass', 'SoundOverrideSubclass', 'name', 'displayid',
        'Quality', 'Flags', 'FlagsExtra', 'Unk430_1', 'Unk430_2', 'BuyCount', 'BuyPrice',
        'SellPrice', 'InventoryType', 'AllowableClass', 'AllowableRace', 'ItemLevel',
        'RequiredLevel', 'RequiredSkill', 'RequiredSkillRank', 'requiredspell',
        'requiredhonorrank', 'RequiredCityRank', 'RequiredReputationFaction',
        'RequiredReputationRank', 'maxcount', 'stackable', 'ContainerSlots',
        'StatsCount', 'stat_type1', 'stat_value1', 'stat_type2', 'stat_value2',
        'stat_type3', 'stat_value3', 'stat_type4', 'stat_value4', 'stat_type5',
        'stat_value5', 'stat_type6', 'stat_value6', 'stat_type7', 'stat_value7',
        'stat_type8', 'stat_value8', 'stat_type9', 'stat_value9', 'stat_type10',
        'stat_value10', 'ScalingStatDistribution', 'ScalingStatValue', 'dmg_min1',
        'dmg_max1', 'dmg_type1', 'dmg_min2', 'dmg_max2', 'dmg_type2', 'armor',
        'holy_res', 'fire_res', 'nature_res', 'frost_res', 'shadow_res', 'arcane_res',
        'delay', 'ammo_type', 'RangedModRange', 'spellid_1', 'spelltrigger_1',
        'spellcharges_1', 'spellppmRate_1', 'spellcooldown_1', 'spellcategory_1',
        'spellcategorycooldown_1', 'spellid_2', 'spelltrigger_2', 'spellcharges_2',
        'spellppmRate_2', 'spellcooldown_2', 'spellcategory_2', 'spellcategorycooldown_2',
        'spellid_3', 'spelltrigger_3', 'spellcharges_3', 'spellppmRate_3',
        'spellcooldown_3', 'spellcategory_3', 'spellcategorycooldown_3', 'spellid_4',
        'spelltrigger_4', 'spellcharges_4', 'spellppmRate_4', 'spellcooldown_4',
        'spellcategory_4', 'spellcategorycooldown_4', 'spellid_5', 'spelltrigger_5',
        'spellcharges_5', 'spellppmRate_5', 'spellcooldown_5', 'spellcategory_5',
        'spellcategorycooldown_5', 'bonding', 'description', 'PageText', 'LanguageID',
        'PageMaterial', 'startquest', 'lockid', 'Material', 'sheath', 'RandomProperty',
        'RandomSuffix', 'block', 'itemset', 'MaxDurability', 'area', 'Map', 'BagFamily',
        'TotemCategory', 'socketColor_1', 'socketContent_1', 'socketColor_2',
        'socketContent_2', 'socketColor_3', 'socketContent_3', 'socketBonus',
        'GemProperties', 'RequiredDisenchantSkill', 'ArmorDamageModifier', 'duration',
        'ItemLimitCategory', 'HolidayId', 'ScriptName', 'DisenchantID', 'FoodType',
        'minMoneyLoot', 'maxMoneyLoot', 'flagsCustom', 'VerifiedBuild'
    ];

    protected $casts = [
        'entry' => 'integer',
        'class' => 'integer',
        'subclass' => 'integer',
        'SoundOverrideSubclass' => 'integer',
        'Quality' => 'integer',
        'Flags' => 'integer',
        'FlagsExtra' => 'integer',
        'Unk430_1' => 'integer',
        'Unk430_2' => 'integer',
        'BuyCount' => 'integer',
        'BuyPrice' => 'integer',
        'SellPrice' => 'integer',
        'InventoryType' => 'integer',
        'AllowableClass' => 'integer',
        'AllowableRace' => 'integer',
        'ItemLevel' => 'integer',
        'RequiredLevel' => 'integer',
        'RequiredSkill' => 'integer',
        'RequiredSkillRank' => 'integer',
        'requiredspell' => 'integer',
        'requiredhonorrank' => 'integer',
        'RequiredCityRank' => 'integer',
        'RequiredReputationFaction' => 'integer',
        'RequiredReputationRank' => 'integer',
        'maxcount' => 'integer',
        'stackable' => 'integer',
        'ContainerSlots' => 'integer',
        'StatsCount' => 'integer',
        'stat_type1' => 'integer',
        'stat_value1' => 'integer',
        'stat_type2' => 'integer',
        'stat_value2' => 'integer',
        'stat_type3' => 'integer',
        'stat_value3' => 'integer',
        'stat_type4' => 'integer',
        'stat_value4' => 'integer',
        'stat_type5' => 'integer',
        'stat_value5' => 'integer',
        'stat_type6' => 'integer',
        'stat_value6' => 'integer',
        'stat_type7' => 'integer',
        'stat_value7' => 'integer',
        'stat_type8' => 'integer',
        'stat_value8' => 'integer',
        'stat_type9' => 'integer',
        'stat_value9' => 'integer',
        'stat_type10' => 'integer',
        'stat_value10' => 'integer',
        'ScalingStatDistribution' => 'integer',
        'ScalingStatValue' => 'integer',
        'dmg_min1' => 'integer',
        'dmg_max1' => 'integer',
        'dmg_type1' => 'integer',
        'dmg_min2' => 'integer',
        'dmg_max2' => 'integer',
        'dmg_type2' => 'integer',
        'armor' => 'integer',
        'holy_res' => 'integer',
        'fire_res' => 'integer',
        'nature_res' => 'integer',
        'frost_res' => 'integer',
        'shadow_res' => 'integer',
        'arcane_res' => 'integer',
        'delay' => 'integer',
        'ammo_type' => 'integer',
        'RangedModRange' => 'float',
        'spellid_1' => 'integer',
        'spelltrigger_1' => 'integer',
        'spellcharges_1' => 'integer',
        'spellppmRate_1' => 'float',
        'spellcooldown_1' => 'integer',
        'spellcategory_1' => 'integer',
        'spellcategorycooldown_1' => 'integer',
        'spellid_2' => 'integer',
        'spelltrigger_2' => 'integer',
        'spellcharges_2' => 'integer',
        'spellppmRate_2' => 'float',
        'spellcooldown_2' => 'integer',
        'spellcategory_2' => 'integer',
        'spellcategorycooldown_2' => 'integer',
        'spellid_3' => 'integer',
        'spelltrigger_3' => 'integer',
        'spellcharges_3' => 'integer',
        'spellppmRate_3' => 'float',
        'spellcooldown_3' => 'integer',
        'spellcategory_3' => 'integer',
        'spellcategorycooldown_3' => 'integer',
        'spellid_4' => 'integer',
        'spelltrigger_4' => 'integer',
        'spellcharges_4' => 'integer',
        'spellppmRate_4' => 'float',
        'spellcooldown_4' => 'integer',
        'spellcategory_4' => 'integer',
        'spellcategorycooldown_4' => 'integer',
        'spellid_5' => 'integer',
        'spelltrigger_5' => 'integer',
        'spellcharges_5' => 'integer',
        'spellppmRate_5' => 'float',
        'spellcooldown_5' => 'integer',
        'spellcategory_5' => 'integer',
        'spellcategorycooldown_5' => 'integer',
        'bonding' => 'integer',
        'PageText' => 'integer',
        'LanguageID' => 'integer',
        'PageMaterial' => 'integer',
        'startquest' => 'integer',
        'lockid' => 'integer',
        'Material' => 'integer',
        'sheath' => 'integer',
        'RandomProperty' => 'integer',
        'RandomSuffix' => 'integer',
        'block' => 'integer',
        'itemset' => 'integer',
        'MaxDurability' => 'integer',
        'area' => 'integer',
        'Map' => 'integer',
        'BagFamily' => 'integer',
        'TotemCategory' => 'integer',
        'socketColor_1' => 'integer',
        'socketContent_1' => 'integer',
        'socketColor_2' => 'integer',
        'socketContent_2' => 'integer',
        'socketColor_3' => 'integer',
        'socketContent_3' => 'integer',
        'socketBonus' => 'integer',
        'GemProperties' => 'integer',
        'RequiredDisenchantSkill' => 'integer',
        'ArmorDamageModifier' => 'float',
        'duration' => 'integer',
        'ItemLimitCategory' => 'integer',
        'HolidayId' => 'integer',
        'DisenchantID' => 'integer',
        'FoodType' => 'integer',
        'minMoneyLoot' => 'integer',
        'maxMoneyLoot' => 'integer',
        'flagsCustom' => 'integer',
        'VerifiedBuild' => 'integer'
    ];

    /**
     * Get item quality color
     */
    public function getQualityColorAttribute()
    {
        $qualityColors = config('wow.quality_colors');
        return $qualityColors[$this->Quality] ?? '#ffffff';
    }

    /**
     * Get item icon URL
     */
    public function getIconUrlAttribute()
    {
        if (!empty($this->icon)) {
            return "https://wow.zamimg.com/images/wow/icons/large/" . strtolower($this->icon) . ".jpg";
        }
        return asset('img/characterarmor/inv_misc_questionmark.gif');
    }
}
