@section('title')
{{ $itembunch[0]->rawName }} - Cataclysm: Dark Days Ahead
@endsection
@section('description')
{{ $itembunch[0]->rawName }} has a volume of {{ $itembunch[0]->volume }} and a weight of {{ $itembunch[0]->weight }}. It does {{ $itembunch[0]->bashing }} bashing damage and {{ $itembunch[0]->cutting }} cutting damage. You can find more information here.
@endsection
@if($itembunch[0]->isVehiclePart)
  @include('items.vpart_menu', array('active'=>'view'))
@else
  @include('items.menu', array('active'=>'view'))
@endif
@foreach($itembunch as $item)
  <div class="row">
    <div class="col-md-8">
      <h1>{!!$item->symbol!!} {{ $item->name }} {!!$item->modLabel!!}</h1>
      {!!$item->featureLabels!!}
      @if($item->isVehiclePart)
        <br>
        伤害系数：{{ $item->damage_modifier }} <br>
        耐久：{{ $item->durability }} <br>
        滚动阻力：{{ $item->rolling_resistance }} <br>
        轮胎类型：{{ $item->wheel_type }} <br>
        <br>
        这个车辆部件是由 {!!$item->sourcePart!!} 安装而得到的。
        <br>
        <br>
      @endif
      @if(!$item->isVehiclePart)
        <br>
        材质：{!! $item->materials !!}<br>
        体积：<yellow>{{ $item->volume }}</yellow> 升&nbsp;&nbsp;重量：<yellow>{{ $item->weightMetric }}</yellow> 千克<br>
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
            &nbsp;斩击：<yellow>{{ $item->cutting }}</yellow>
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
          {{ $cutResult['amount'] }} <a href="{{ route('item.view', $cutResult['item']->id) }}">{{ str_plural($cutResult['item']->name) }}</a>,
        @endforeach
        <br>
      @endif

      @if($item->isPetArmor)
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
        种植得到：<a href="{{ route('item.view', $item->seed_data->fruit) }}">{{ gettext($item->seed_data->plant_name) }}</a><br>
        成熟时间：{{ $item->seed_data->grow }}<br>
      @endif

      @if($item->isBionicItem)
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
        可用于：{!! $item->usedby !!}
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
        --<br>
        弹药：{{ $item->clip_size }} 回合：<br>
        <table class="tablesorter">
          <thead>
            <tr>
              <th>弹药</th>
              <th style="width: 4em" class="text-right">伤害</th>
              <th style="width: 4em" class="text-right">穿甲</th>
              <th style="width: 4em" class="text-right">噪音</th>
            </tr>
          </thead>
          @if($item->hasAmmoTypes)
            @foreach($item->ammoTypes as $ammo)
              <tr>
                <td><a href="{{ route("item.view", $ammo->id) }}">{{ $ammo->name }}</a></td>
                <td class="text-right">{{ $ammo->damage }}</td>
                <td class="text-right">{{ $ammo->pierce }}</td>
                <td class="text-right">{{ round($item->noise($ammo)) }}</td>
              </tr>
            @endforeach
          @endif
        </table>
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
        --<br>
        可用：{!!$item->modSkills!!}<br>
        位置：{{ $item->location }}<br>
      @endif

      @if($item->isConsumable)
        --<br>
        状态：{{ $item->phase }}<br>
        <span title="(*actual amounts may depend on components)">热量 (千卡)</span>：<yellow>{{ $item->nutrition }}</yellow>
        &nbsp;解渴：<yellow>{{ $item->quench }}</yellow><br>
        享受：<yellow>{{ $item->fun }}</yellow><br>
        @if($item->spoils_in>0)
          保质期：<yellow>{{ $item->spoils_in }}</yellow><br>
        @endif
        分量：<yellow>{{ $item->charges }}</yellow><br>
        健康：<yellow>{{ $item->healthy }}</yellow><br>
        兴奋剂：<yellow>{{ $item->stim }}</yellow><br>
        上瘾：<yellow>{{ $item->addiction_potential }}</yellow><br>
      @endif
      @if($item->isArmor)
        --<br>
        覆盖：{!! $item->covers !!}<br>
        覆盖率：<yellow>{{ $item->coverage }}</yellow>%
        &nbsp;保暖度：<yellow>{{ $item->warmth }}</yellow><br>
        --<br>
        材料厚度：<yellow>{{ $item->material_thickness }}</yellow>mm<br>
        累赘度：<yellow>{!! $item->encumbrance !!}</yellow>
        &nbsp;容积：{!! $item->storage !!}<br>
        防护：
        钝击：<yellow>{{ $item->protection('bash') }}</yellow>
        &nbsp;斩击：<yellow>{{ $item->protection('cut') }}</yellow><br>
        &nbsp;防酸：<yellow>{{ $item->protection('acid') }}</yellow>
        &nbsp;防火：<yellow>{{ $item->protection('fire') }}</yellow>
        &nbsp;环境：<yellow>{{ $item->environmental_protection }}</yellow><br>
      @endif

      @if($item->isContainer)
        @if($item->rigid=='R')
          This item is rigid.<br>
        @endif
        @if($item->seals=='S')
          这个容器能能<info>重新封装</info>。<br>
        @endif
        @if($item->watertight=='W')
          这个容器是<info>水密</info>的。<br>
        @endif
        @if($item->preserves=='P')
          这个容器能<good>防止腐坏</good>。<br>
        @endif
        这个容器能储存{{ $item->contains }}升液体。<br>
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
      @if($item->isBrewable)
        {!!$item->brewable!!}
        <br>
      @endif
      @if($item->hasFlag('DISABLE_SIGHTS'))
        * 这个模组<bad>阻挡</bad>主武器的<bad>视线</bad>。<br>
      @endif
      @if($item->hasFlag('EATEN_COLD'))
        * 这件食物<info>冷藏</info>后<good>风味更佳</good>。<br>
      @endif
      @if($item->hasFlag("FIT"))
        * 这件装备很<info>合身</info>。<br>
      @endif
      @if($item->hasFlag("OVERSIZE"))
        * 这件装备尺码足够大，能够容纳下 <info>大型变异肢体</info>。<br>
      @endif
      @if($item->hasFlag("SKINTIGHT"))
        * 这件衣服属于<info>贴身衣物</info>。<br>
      @endif
      @if($item->hasFlag("POCKETS"))
        * 这件装备有<info>口袋</info>，能在你空手时把手放在口袋里，为手部保暖。<br>
      @endif
      @if($item->hasFlag("HOOD"))
        * 这件装备有<info>兜帽</info>，能在头部没有累赘时戴上兜帽，为头部保暖。<br>
      @endif
      @if($item->hasFlag("RAINPROOF"))
        * 这件装备能够让你在雨中保持<info>干燥</info>。<br>
      @endif
      @if($item->hasFlag("SUN_GLASSES"))
        * 这件装备能<info>防眩光</info>。<br>
      @endif
      @if($item->hasFlag("WATER_FRIENDLY"))
        * 这件装备在<info>湿透</info>时依旧<good>性能良好</good>，不受心情值惩罚。<br>
      @endif
      @if($item->hasFlag("WATERPROOF"))
        * 这件装备<info>不透水</info>，除非你跳进河里或者被水淹没。<br>
      @endif
      @if($item->hasFlag("STURDY"))
        * 这件装备具有<good>良好防护</good>，能使你免于受伤并<info>承受大量伤害</info>。<br>
      @endif
      @if($item->hasFlag("SWIM_GOGGLES"))
        * 这件装备能让你在<info>水下</info>
        <good>看得更远</good>。<br>
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