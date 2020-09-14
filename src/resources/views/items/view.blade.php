@section('title')
{{ $itembunch[0]->rawName }} - CDDA 物品浏览器
@endsection
@section('description')
{{ $itembunch[0]->rawName }} 的体积为 {{ $itembunch[0]->volume }}，重量为 {{ $itembunch[0]->weight }}。它的钝击伤害为 {{ $itembunch[0]->bashing }}、斩击伤害为 {{ $itembunch[0]->cutting }}。你可以在此找到更多相关信息。
@endsection
@if($itembunch[0]->isVehiclePart)
  @include('items.vpart_menu', array('active'=>'view'))
@else
  @include('items.menu', array('active'=>'view'))
@endif
@foreach($itembunch as $item)
  <div class="row">
    <div class="col-md-8">
      <h1>{!!$item->symbol!!} {{ $item->rawname }} {!!$item->modLabel!!}</h1>
      {!!$item->featureLabels!!}
      @if($item->isVehiclePart)
        <br>
        伤害系数：<yellow>{{ $item->damage_modifier }}</yellow><br>
        耐久：<yellow>{{ $item->durability }}</yellow><br>
        @if($item->size)
          @if($item->hasFlag("FLUIDTANK"))
            容量：<yellow>{{ $item->size / 1000 }}</yellow> 升<br>
          @else
            空间：<yellow>{{ $item->size / 4 }}</yellow> 升<br>
          @endif
        @endif
        @if($item->cargo_weight_modifier !== NULL)
          载重系数：<yellow>{{ $item->cargo_weight_modifier }}</yellow>%<br>
        @endif
        @if($item->rolling_resistance)
          滚动阻力：{{ $item->rolling_resistance }}<br>
        @endif
        @if($item->wheel_type)
          轮胎类型：{{ $item->wheel_type }}<br>
        @endif
        @if($item->power)
          功率：<yellow>{{ $item->power }}</yellow><br>
        @endif
        @if($item->energy_consumption)
          能量消耗：<yellow>{{ $item->energy_consumption }}</yellow><br>
        @endif
        @if($item->epower)
          电力生产：<yellow>{{ $item->epower }}</yellow><br>
        @endif
        @if($item->hasFlag("ENGINE"))
          --<br>
            巡航功率：<yellow>{{ $item->m2c }}</yellow>%<br>
          @if($item->backfire_threshold > 0)
            逆火耐久：<yellow>{{ $item->backfire_threshold * 100 }}</yellow>%<br>
            逆火概率：<yellow>{{ round(1 / $item->backfire_freq * 100, 2) }}</yellow>%<br>
          @endif
          噪音系数：<yellow>{{ $item->noise_factor }}</yellow><br>
          @if($item->damaged_power_factor)
            伤害功率下降系数：<yellow>{{ $item->damaged_power_factor }}</yellow><br>
          @endif
          @if($item->muscle_power_factor)
            肌肉能量转换系数：<yellow>{{ $item->muscle_power_factor }}</yellow><br>
          @endif
        @else
          --<br>
          @if($item->comfort)
            舒适度：<yellow>{{ $item->comfort ?: 0 }}</yellow><br>
          @endif
          @if($item->bonus_fire_warmth_feet)
            火焰温度奖励：<yellow>{{ $item->bonus_fire_warmth_feet ?: 0 }}</yellow><br>
          @endif
          @if($item->floor_bedding_warmth)
            地板睡眠温度加成：<yellow>{{ $item->floor_bedding_warmth ?: 0 }}</yellow><br>
          @endif
        @endif
        --<br>
        破坏产生：{!! $item->breaks_into !!}<br>
        @if($item->description)
          --<br>
          {{ $item->description }}<br>
        @endif
        --<br>
        这个车辆部件是由 {!!$item->sourcePart!!} 安装而得到的。<br>
      @endif
      @if(!$item->isVehiclePart)
        <br>
        材质：{!! $item->materials !!}<br>
        体积：<yellow>{{ $item->volume }}</yellow> 升&nbsp;&nbsp;重量：<yellow>{{ $item->weightMetric }}</yellow> 千克<br>
        长度：{!! $item->longest_side !!}<br>
        --<br>
        {{ $item->description }}<br>
        @if($item->min_strength)
          --<br>
          技能要求：力量 <yellow>{{ $item->min_strength }}</yellow><br>
        @endif
        @if($item->bashing + $item->piercing +  $item->cutting != 0)
          --<br>
          近战伤害：
          钝击：<yellow>{{ $item->bashing }}</yellow>
          @if($item->hasFlag("SPEAR"))
            &nbsp;刺击：<yellow>{{ $item->piercing }}</yellow>
          @elseif($item->hasFlag("STAB"))
            &nbsp;刺击：<yellow>{{ $item->cutting }}</yellow>
          @else
            &nbsp;斩击：<yellow>{{ $item->cutting }}</yellow>
          @endif
          &nbsp;命中：<yellow>{{ $item->to_hit }}</yellow><br>
          攻击消耗行动点：<yellow>{{ $item->movesPerAttack }}</yellow><br>
          平均每回合伤害：<yellow>{{ $item->damagePerMove }}</yellow><br>
        @endif
        @if($item->hasTechniques)
          --<br>
          手持战技：{!! $item->techniques !!}<br>
        @endif
        @if($item->hasFlag("REACH_ATTACK"))
          --<br>
          @if($item->hasFlag("REACH3"))
            * 这件物品为 <stat>长远距攻击</stat>
          @else
            * 这件物品为 <stat>远距攻击</stat>
          @endif
          <br>
        @endif
      @endif
      @if($item->type == "WHEEL")
        --<br>
        直径：{{ $item->diameter }}<br>
        宽度：{{ $item->width }}<br>
      @endif
      @if(count($item->qualities))
        --<br>
        @foreach($item->qualities as $quality)
          具有 <yellow>{{ $quality["level"] }}</yellow> 级 <a href="{{ route("item.qualities", $quality["quality"]->id) }}">{{ $quality["quality"]->name }}</a> 特性。<br>
        @endforeach
      @endif
      @if($item->canBeCut)
        可以被切割为：
        @foreach($item->cutResult as $cutResult)
          {{ $cutResult['amount'] }} <a href="{{ route('item.view', $cutResult['item']->id) }}">{{ $cutResult['item']->name }}</a>,
        @endforeach
        <br>
      @endif

      @if($item->isPetArmor)
        --<br>
        @php
          $pet = isset($item->pet_armor_data) ? $item->pet_armor_data : $item;
          function parse_storage($storage) {
          if (stripos($storage, "ml")) {
          return (floatval($storage) / 1000.0);
          } else if (strpos($storage, "L")) {
          return (floatval($storage)* 1.0);
          }
          return (floatval($storage) / 4.0);
          }
        @endphp
        适用宠物：<yellow>{{ $pet->pet_bodytype }}</yellow><br>
        最大宠物体积：<yellow>{{ parse_storage($pet->max_pet_vol) }}</yellow> L<br>
        最小宠物体积：<yellow>{{ parse_storage($pet->min_pet_vol) }}</yellow> L<br>
        环境防护：<yellow>{{ $pet->environmental_protection ?: 0 }}</yellow> <br>
        材料厚度：<yellow>{{ $pet->material_thickness }}</yellow> mm<br>
      @endif

      @if($item->seed_data)
        --<br>
        种植得到：<a href="{{ route('item.view', $item->seed_data->fruit) }}">{{ gettext($item->seed_data->plant_name) }}</a><br>
        成熟时间：{{ $item->seed_data->grow }}<br>
      @endif

      @if($item->isBionic)
        --<br>
        @if($item->getbodyparts("occupied_bodyparts"))
          占据槽位：{!! $item->getbodyparts("occupied_bodyparts") !!} <br>
        @endif
        @if($item->getbodyparts("encumbrance"))
          累赘：{!! $item->getbodyparts("encumbrance") !!} <br>
        @endif
        @if($item->getbodyparts("env_protec"))
          环境防护：{!! $item->getbodyparts("env_protec") !!} <br>
        @endif
        @if($item->getbodyparts("bash_protec"))
          钝击防护：{!! $item->getbodyparts("bash_protec") !!} <br>
        @endif
        @if($item->getbodyparts("cut_protec"))
          斩击防护：{!! $item->getbodyparts("cut_protec") !!} <br>
        @endif
        @if($item->fuel_options)
          燃料：{!! $item->fuel_options !!}<br>
        @endif
        @if($item->fuel_capacity > 0)
          燃料容量：<yellow>{{ $item->fuel_capacity }}</yellow> 毫升<br>
        @endif
        @if($item->fuel_efficiency > 0)
          燃料效率：<yellow>{{ $item->fuel_efficiency }}</yellow><br>
        @endif
      @endif

      @if($item->fake_item != NULL)
        --<br>
        对应物品：<a href="{{ route("item.view", $item->fake_item->id) }}">{{ $item->fake_item->name }}</a><br>
      @endif

      @if($item->isBionicItem)
        --<br>
        安装难度：{{ $item->difficulty }}
        <br>
      @endif

      @if($item->isAmmo)
        --<br>
        伤害：<yellow>{{ $item->damage }}</yellow>
        &nbsp;穿甲：<yellow>{{ $item->pierce }}</yellow><br>
        射程：<yellow>{{ $item->range }}</yellow>
        &nbsp;散布：<yellow>{{ $item->dispersion }}</yellow><br>
        后坐力：<yellow>{{ $item->recoil }}</yellow><br>
        伤害加成：<yellow>{{ $item->prop_damage }}</yellow><br>
        数量：<yellow>{{ $item->count }}</yellow><br>
        可用于：{!! $item->usedby !!}<br>
      @endif

      @if($item->fuel != NULL)
        --<br>
        能量比：<yellow>{{ $item->fuel->energy }}</yellow> 单位/毫升<br>
        {{-- 此处 isset 不可？ --}}
        @if(array_key_exists("explosion_data", $item->fuel))
          爆炸几率：
          热武器攻击：<yellow>{{ $item->fuel->explosion_data->chance_hot / 100 }}</yellow>%&nbsp;
          冷兵器攻击：<yellow>{{ $item->fuel->explosion_data->chance_cold / 100 }}</yellow>%<br>
          爆炸威力：<yellow>{{ $item->fuel->explosion_data->factor }}</yellow><br>
        @endif
      @endif

      @if($item->isTool)
        --<br>
        最大 {{ $item->max_charges }} 单位
        @if($item->ammo!="NULL")
          的：
          @foreach($item->ammoTypes as $ammo)
            <a href="{{ route("item.view", $ammo->id) }}">{{ $ammo->name }}</a>,
          @endforeach
        @endif
        <br>
      @endif
      @if($item->isGun)
        @if($item->hasAmmoTypes)
          --<br>
          弹药：{{ $item->clip_size }} 回合：<br>
          <table class="tablesorter">
            <thead>
              <tr>
                <th>弹药</th>
                <th style="width: auto" class="text-right">伤害（暴击）</th>
                <th style="width: 4em" class="text-right">穿甲</th>
                <th style="width: 4em" class="text-right">噪音</th>
              </tr>
            </thead>
            @foreach($item->ammoTypes as $ammo)
              <tr>
                <td><a href="{{ route("item.view", $ammo->id) }}">{{ $ammo->name }}</a></td>
                <td class="text-right">{{ $ammo->damage }}
                  @if($ammo->critical_multiplier > 0)
                    ({{ $ammo->damage * $ammo->critical_multiplier }})
                  @endif
                </td>
                <td class="text-right">{{ $ammo->pierce }}</td>
                <td class="text-right">{{ round($item->noise($ammo)) }}</td>
              </tr>
            @endforeach
          </table>
        @endif
        --<br>
        基本远程伤害：<yellow>{{ $item->ranged_damage }}</yellow><br>
        射程：<yellow>{{ $item->range }}</yellow><br>
        穿甲：<yellow>{{ $item->pierce }}</yellow><br>
        散布：<yellow>{{ $item->dispersion }}</yellow><br>
        后坐力：<yellow>{{ $item->recoil }}</yellow><br>
        装填耗时：<yellow>{{ $item->reload }}</yellow><br>
        @if($item->modes)
          --<br>
          射击模式：{!! $item->modes !!}<br>
        @endif
        @if($item->burst !=0 )
          连发大小：<yellow>{{ $item->burst }}</yellow><br>
        @endif
        @if($item->isModdable)
          --<br>
          模组：<br>
          {!! $item->validModLocations !!}<br>
        @endif
      @endif

      @if($item->isGunMod)
        --<br>
        @if($item->min_skills)
          技能需求：{{ $item->min_skills }}<br>
        @endif
        @if($item->sight_dispersion)
          瞄准散布：<yellow>{{ $item->sight_dispersion }}</yellow><br>
        @endif
        @if($item->aim_speed)
          瞄准速度：<yellow>{{ $item->aim_speed }}</yellow><br>
        @endif
        @if($item->dispersionModifier != 0)
          散布修正：<yellow>{{ $item->dispersionModifier }}</yellow><br>
        @endif
        @if($item->handlingModifier != 0)
          操作性修正：<yellow>{{ $item->handlingModifier }}</yellow><br>
        @endif
        @if($item->loudnessModifier != 0)
          噪音修正：<yellow>{{ $item->loudnessModifier }}</yellow><br>
        @endif
        @if($item->damageModifier!=0)
          伤害：<yellow>{{ $item->damageModifier }}</yellow><br>
        @endif
        @if($item->clipSizeModifier!=0)
          弹匣：{{ $item->clipSizeModifier }}%<br>
        @endif
        @if($item->rangeModifier != 0)
          范围：<yellow>{{ $item->rangeModifier }}</yellow><br>
        @endif
        @if($item->recoilModifier!=0)
          后坐力：<yellow>{{ $item->recoilModifier }}</yellow><br>
        @endif
        @if($item->burstModifier!=0)
          连发伤害：<yellow>{{ $item->burstModifier }}</yellow><br>
        @endif
        @if($item->ammoModifier)
          弹药：{!! implode(", ", $item->ammoModifier) !!}<br>
        @endif
        @if($item->ups_charges_multiplier != NULL)
          UPS 能量消耗：x<yellow>{{ $item->ups_charges_multiplier }}</yellow><br>
        @endif
        --<br>
        可用：{!!$item->modSkills!!}<br>
        位置：{{ $item->location }}<br>
      @endif

      @if($item->isConsumable)
        --<br>
        状态：{{ $item->phase }}<br>
        <span title="(*actual amounts may depend on components)">热量</span>：<yellow>{{ $item->nutrition }}</yellow> 千卡
        &nbsp;解渴：<yellow>{{ $item->quench }}</yellow><br>
        享受：<yellow>{{ $item->fun }}</yellow>&nbsp;
        健康：<yellow>{{ $item->healthy }}</yellow><br>
        分量：<yellow>{{ $item->charges }}</yellow><br>
        @if($item->spoils_in>0)
          保质期：<yellow>{{ $item->spoils_in }}</yellow><br>
        @endif
        @if($item->hasKey("vitamins"))
          维生素：{!! $item->vitamins !!}<br>
        @endif
        兴奋剂：<yellow>{{ $item->stim }}</yellow>&nbsp;
        上瘾概率：<yellow>{{ $item->addiction_potential }}</yellow>%<br>
      @endif

      @if($item->hasKey("rot_spawn"))
        --<br>
        孵化产物：{!! $item->rot_spawn !!}<br>
        孵化概率：<yellow>{{ round($item->rot_spawn_chance, 1) }}</yellow>%<br>
      @endif

      @if($item->isArmor)
        --<br>
        覆盖：{!! $item->covers !!}<br>
        衣物层：<info>{{ $item->clothing_layer }}</info><br>
        覆盖率：<yellow>{{ $item->coverage }}</yellow>%
        &nbsp;保暖度：<yellow>{{ $item->warmth }}</yellow><br>
        --<br>
        材料厚度：<yellow>{{ $item->material_thickness }}</yellow>mm<br>
        累赘度：{!! $item->encumbrance !!}<br>
        防护：
        钝击：<yellow>{{ $item->protection('bash') }}</yellow>
        &nbsp;斩击：<yellow>{{ $item->protection('cut') }}</yellow>
        &nbsp;防弹：<yellow>{{ $item->protection('bullet') }}</yellow><br>
        &nbsp;防电：<yellow>{{ $item->protection('elec') }}</yellow>
        &nbsp;防酸：<yellow>{{ $item->protection('acid') }}</yellow>
        &nbsp;防火：<yellow>{{ $item->protection('fire') }}</yellow>
        &nbsp;环境：<yellow>{{ $item->environmental_protection }}</yellow><br>
      @endif

      @if($item->power_draw != NULL)
        --<br>
        能量消耗：<y>{{ $item->power_draw / 1000 }}</y>W<br>
      @endif

      @if($item->pocket_data !== NULL)
        @foreach ($item->pocket_data as $k => $pocket)
          --<br>
          @if (count($item->pocket_data) > 1)
            {{ "口袋 ".($k + 1)."：" }}
            <br>
          @endif
          @if (($pocket->pocket_type ?? 'CONTAINER') == "MAGAZINE_WELL")
            兼容弹匣：{!! $item->get_item_restriction($k) !!}<br>
            @continue
          @endif
          @if (($pocket->pocket_type ?? 'CONTAINER') == "MAGAZINE")
            {!! $item->get_ammo_restriction($k) !!}<br>
            @continue
          @endif
          @if (isset($pocket->max_contains_volume))
            最大容量：<yellow>{{ $pocket->max_contains_volume }} </yellow>L<br>
          @endif
          @if (isset($pocket->max_contains_weight))
            最大重量：<yellow>{{ $pocket->max_contains_weight }} </yellow>千克<br>
          @endif
          @if (isset($pocket->min_item_volumn))
            最小物品体积：<yellow>{{ $pocket->min_item_volumn }} </yellow>L<br>
          @endif
          @if (isset($pocket->min_item_volumn))
            最大物品体积：<yellow>{{ $pocket->max_item_volumn }} </yellow>L<br>
          @endif
          @if (isset($pocket->max_item_length))
            最大物品长度：<yellow>{{ $pocket->max_item_length }} </yellow> 米<br>
          @elseif(isset($pocket->max_contains_volume))
            最大物品长度：<yellow>{{
              round(pow($pocket->max_contains_volume, 1.0 / 3) * sqrt(2.0), 3)
            }}
            </yellow>米<br>
          @endif
          @if (isset($pocket->spoil_multiplier))
            腐烂速度：<yellow>{{ $pocket->spoil_multiplier }}</yellow><br>
          @endif
          @if (isset($pocket->sealed_data))
            @if (isset($pocket->sealed_data->spoil_multiplier))
              腐烂速度（密封）：<yellow>{{ $pocket->sealed_data->spoil_multiplier }}</yellow> <br>
            @endif
          @endif
          @if (isset($pocket->weight_multiplier))
            重量系数：<yellow>{{ $pocket->weight_multiplier }}</yellow><br>
          @endif
          @if (isset($pocket->volume_multiplier))
            体积系数：<yellow>{{ $pocket->volume_multiplier }}</yellow><br>
          @endif
          @if (isset($pocket->magazine_well))
            原始容积：<yellow>{{ $pocket->magazine_well }}</yellow><br>
          @endif
          取出物品基础耗时：<yellow>{{ $pocket->moves ?? 100 }}</yellow><br>
          @if ($pocket->rigid ?? true)
            * 这个容器足够 <info>坚硬</info>。<br>
          @endif
          @if ($pocket->watertight ?? false)
            * 这个容器可以容纳 <info>液体</info>。<br>
          @endif
          @if ($pocket->airtight ?? false)
            * 这个容器可以容纳 <info>气体</info>。<br>
          @endif
          @if ($pocket->open_container ?? false)
            * 穿戴或将其放入其他物品中会使 <bad>内容物掉落</bad>。<br>
          @endif
        @endforeach
        {{-- TODO: 剩余部分，如弹夹类型 --}}
      @endif

      @if($item->isBook)
        --<br>
        @if($item->skill=="none")
          仅供娱乐。<br>
        @else
          可以提升你的 <info>{{ $item->skill }}</info> 技能到 <yellow>{{ $item->max_level }}</yellow> 级<br>
          @if($item->required_level==0)
            适合<info>初学者</info>阅读。.<br>
          @else
            需要 <info>{{ $item->skill }}</info> 技能 <yellow>{{ $item->required_level }}</yellow> 级才能理解。<br>
          @endif
        @endif
        需要 <info>智力</info>
        <yellow>{{ $item->intelligence }}</yellow> 点才能轻松阅读。<br>
        @if($item->fun!=0)
          阅读此书对会使你的心情值 <yellow>{{ $item->fun }}</yellow> <br>
        @endif
        阅读此书的一个章节需要 <yellow>{{ $item->time / 60 }}</yellow>
        <info>分钟</info>。<br>
        @if($item->chapters)
          章节：{{ $item->chapters }}.<br>
        @endif
        @if($item->count("learn") != 0)
          --<br>
          这本书包含 {{ $item->count("learn") }} 个配方：
          {!! $item->craftingRecipes !!}
          <br>
        @endif
      @endif

      --<br>
      @if($item->book_data !== NULL)
        包含武术：{{ $item->book_data }}<br>
      @endif
      @if($item->isBrewable)
        {!!$item->brewable!!}
        <br>
      @endif
      @if($item->reinforcable)
        * 这件物品可以被 <good>强化</good>。<br>
      @endif
      @if($item->conductive)
        * 这件物品 <bad>导电</bad>。<br>
      @else
        * 这件物品 <good>不导电</good>。<br>
      @endif
      {!! $item->flag_descriptions !!}
      @if($item->exothermic_power_gen)
        * 这件装备在生产能量时会<info>放热</info>。<br>
      @endif
      @if($item->hasFlag("BIONIC_TOGGLED"))
        * 这件装备可以被<info>开关</info>。<br>
      @endif
      @if($item->hasFlag('DISABLE_SIGHTS'))
        * 这个模组 <bad>阻挡</bad> 主武器的 <bad>视线</bad>。<br>
      @endif
      @if($item->hasFlag("FIT"))
        * 这件装备很 <info>合身</info>。<br>
      @endif
      @if($item->hasFlag("LEAK_DAM") && $item->hasFlag("RADIOACTIVE"))
        * 这件物品的外壳已经 <neutral>裂开</neutral> ，露出 <info>不祥的绿光</info>。<br>
      @endif
      @if($item->hasFlag("LEAK_ALWAYS") && $item->hasFlag("RADIOACTIVE"))
        * 这件物品 <neutral>发出</neutral> 了 <info>奇异的绿光</info>。<br>
      @endif

      --<br>
      价格：{!! "$<yellow>".$item->price."</yellow>" !!}
      @if($item->price_postapoc)
        交换价值：{!! "$<yellow>".$item->price_postapoc."</yellow>" !!}
      @endif
      <br>

      @if($item->hasVpartlist)
        --<br>
        这件物品可以安装到载具上，作为：<br>
        {!!$item->VpartFor!!}<br>
      @endif

      --<br>
      @if($item->hasFlags)
        Flags：{!! $item->flags !!}<br>
      @endif
      @if($item->isResultOfCutting)
        可以通过切割 <a href="{{ route('item.materials', $item->materialToCut) }}">{{ $item->materialToCut }} </a>材质的物品获得<br>
      @endif
      @if($item->count("disassembledFrom"))
        可以通过拆解以下物品获得：
        @foreach($item->disassembledFrom as $recipe)
          <a href="{{ route('item.disassemble', $recipe->result->id) }}">{{ $recipe->result->name }}</a>,
        @endforeach
        <br>
      @endif
      @if($item->count("deconstructFrom"))
        可以通过拆解以下家具获得：
        {{ implode(", ", $item->deconstructFrom) }}
        <br>
      @endif
      @if($item->count("bashFromTerrain"))
        可以通过破坏以下特殊地形获得：
        {{ implode(", ", $item->bashFromTerrain) }}
        <br>
      @endif
      {!! $item->dropfrom !!}
      {!! $item->harvestfrom !!}
      <br>
      <details>
        <summary>查看 JSON</summary>
        {!!$item->json!!}
      </details>
    </div>
  </div>
@endforeach
<script>
  $(function() {
    $(".tablesorter").tablesorter({
      sortList: [
        [1, 0]
      ]
    });
  });
  document.addEventListener('DOMContentLoaded', (event) => {
    document.querySelectorAll('pre code').forEach((block) => {
      hljs.highlightBlock(block);
    });
  });
</script>