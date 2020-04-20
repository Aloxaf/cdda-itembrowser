@section('title')
{{{$itembunch[0]->rawName}}} - Cataclysm: Dark Days Ahead
@endsection
@section('description')
{{{$itembunch[0]->rawName}}} has a volume of {{{ $itembunch[0]->volume }}} and a weight of {{{ $itembunch[0]->weight }}}. It does {{{ $itembunch[0]->bashing }}} bashing damage and {{{ $itembunch[0]->cutting }}} cutting damage. You can find more information here.
@endsection
@if($itembunch[0]->isVehiclePart)
@include('items.vpart_menu', array('active'=>'view'))
@else
@include('items.menu', array('active'=>'view'))
@endif
@foreach($itembunch as $item)
<div class="row">
  <div class="col-md-8">
    <h1>{!!$item->symbol!!} {{$item->name}} {!!$item->modLabel!!}</h1>
    {!!$item->featureLabels!!}
    @if ($item->isVehiclePart)
    <br>
    伤害系数: {{{ $item->damage_modifier }}} <br>
    耐久: {{{ $item->durability }}} <br>
    滚动阻力: {{{ $item->rolling_resistance }}} <br>
    轮胎类型: {{{ $item->wheel_type }}} <br>
    <br>
    这个车辆部件是由 {!!$item->sourcePart!!} 安装而得到的。
    <br>
    <br>
    @endif
    @if (!$item->isVehiclePart)
    <br>
    <br>
    体积: {{{ $item->volume }}} 升 重量: {{ $item->weightMetric }} 千克<br>
      钝击: {{{ $item->bashing }}}
      @if ($item->hasFlag("SPEAR"))
      刺击: {{{ $item->piercing }}}
      @elseif ($item->hasFlag("STAB"))
      斩击: {{{ $item->cutting }}}
      @else
      斩击: {{{ $item->cutting }}}
      @endif
      命中: {{{ $item->to_hit }}}<br>
      攻击消耗行动点: {{{ $item->movesPerAttack }}}<br>
      平均每回合伤害: {{{ $item->damagePerMove }}}<br>
      材质: {!! $item->materials !!}<br>
    @endif
    @if ($item->type == "WHEEL")
    直径: {{{ $item->diameter }}}<br>
    宽度: {{{ $item->width }}}<br>
    @endif
      @if ($item->hasFlags)
      Flags: {!! $item->flags !!}<br>
      @endif
      @if ($item->hasTechniques)
      Techniques: {{$item->techniques}}<br>
      @endif
      @foreach ($item->qualities as $quality)
      具有 {{{ $quality["level"] }}} 级 <a href="{{ route("item.qualities", $quality["quality"]->id) }}">{{{ $quality["quality"]->name }}}</a> 特性。<br>
      @endforeach
    @if ($item->canBeCut)
    可以被切割为: 
    @foreach($item->cutResult as $cutResult)
      {{{ $cutResult['amount'] }}} <a href="{{ route('item.view', $cutResult['item']->id) }}">{{ str_plural($cutResult['item']->name) }}</a>,
    @endforeach
    <br>
    @endif
    @if ($item->isResultOfCutting)
    可以通过切割 <a href="{{ route('item.materials', $item->materialToCut) }}">{{{ $item->materialToCut }}} </a>材质的物品获得<br>
    @endif

    @if ($item->count("disassembledFrom"))
    可以通过拆解以下物品获得:
    @foreach($item->disassembledFrom as $recipe)
    <a href="{{ route('item.disassemble', $recipe->result->id) }}">{{$recipe->result->name}}</a>,
    @endforeach
    <br>
    @endif

    @if ($item->count("deconstructFrom"))
    可以通过拆解以下家具获得:
    {{{ implode(", ", $item->deconstructFrom) }}}
    <br>
    @endif


    @if ($item->count("bashFromTerrain"))
    可以通过破坏以下特殊地形获得:
    {{{ implode(", ", $item->bashFromTerrain) }}}
    <br>
    @endif
    --
    <br>

    @if ($item->seed_data)
    种植得到：<a href="{{ route('item.view', $item->seed_data->fruit) }}">{{ gettext($item->seed_data->plant_name) }}</a><br>
    成熟时间：{{ $item->seed_data->grow }}<br>
    @endif

    @if ($item->isBionicItem)
    安装难度：{{ $item->difficulty }}
    <br>
    @endif

    @if ($item->isAmmo)
    伤害: {{{ $item->damage }}}<br>
    伤害加成: {{{ $item->prop_damage }}}<br>
    穿甲: {{{ $item->pierce }}}<br>
    射程: {{{ $item->range }}}<br>
    散步: {{{ $item->dispersion }}}<br>
    后坐力: {{{ $item->recoil }}}<br>
    数量: {{{ $item->count }}}<br>
    可用于: {!! $item->usedby !!}
    @endif
    @if ($item->isTool)

    最大 {{ $item->max_charges }} 单位
    @if ($item->ammo!="NULL")
    的: @foreach($item->ammoTypes as $ammo)
      <a href="{{ route("item.view", $ammo->id) }}">{{$ammo->name}}</a>,
    @endforeach
    @endif
    @endif
    <br>
    @if($item->isGun)
    弹药: {{{ $item->clip_size }}} 回合:<br>
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
        <td><a href="{{ route("item.view", $ammo->id) }}">{{$ammo->name}}</a></td>
        <td class="text-right">{{ $ammo->damage }}</td>
        <td class="text-right">{{ $ammo->pierce }}</td>
        <td class="text-right">{{ round($item->noise($ammo)) }}</td>
      </tr>
      @endforeach
    @endif
    </table>
    基本远程伤害: {{{ $item->ranged_damage }}}<br>
    射程: {{{ $item->range }}}<br>
    穿甲: {{{ $item->pierce }}}<br>
    散步: {{{ $item->dispersion }}}<br>
    后坐力: {{{ $item->recoil }}}<br>
    装填耗时: {{{ $item->reload }}}<br>
    @if ($item->burst==0)
    半自动<br>
    @else
    Burst size: {{{$item->burst}}}<br>
    @endif
    @if ($item->isModdable)
      模组:<br>
      {!! $item->validModLocations !!}
    @endif
    @endif

    @if ($item->isGunMod)
    @if ($item->dispersion!=0)
      Dispersion: {{$item->dispersion}}<br>
    @endif

    @if ($item->damageModifier!=0)
      Damage: {{$item->damageModifier}}<br>
    @endif

    @if ($item->clipSizeModifier!=0)
      Magazine: {{$item->clipSizeModifier}}%<br>
    @endif
    @if ($item->recoilModifier!=0)
      Recoil: {{$item->recoilModifier}}<br>
    @endif
    @if ($item->burstModifier!=0)
      Burst: {{$item->burstModifier}}<br>
    @endif
    @if ($item->ammo_modifier!="NULL")
      弹药: {{$item->ammo_modifier}}<br>
    @endif
      可安装于: {!!$item->modSkills!!}<br>
      位置: {{$item->location}}<br>
    @endif

    <br>
    @if ($item->isConsumable)
      状态: {{{ $item->phase }}}<br>
      <span title="(*actual amounts may depend on components)">热量 (千卡)*</span>: {{{ $item->nutrition }}}<br>
      解渴: {{{ $item->quench }}}<br>
      享受: {{{ $item->fun }}}<br>
      @if ($item->spoils_in>0)
      保质期: {{{ $item->spoils_in }}}<br>
      @endif
      分量: {{{ $item->charges }}}<br>
      健康: {{{ $item->healthy }}}<br>
      兴奋剂: {{{ $item->stim }}}<br>
      上瘾: {{{ $item->addiction_potential }}}<br>
    @endif
    @if ($item->isArmor)
      覆盖部位: {!! $item->covers !!}<br>
      覆盖率: {{{ $item->coverage }}}%<br>
      累赘度: {{{ $item->encumbrance }}}<br>
      防护: 钝击:
      {{{ $item->protection('bash') }}}
      斩击:  {{{  $item->protection('cut') }}}<br>
      防酸: {{{  $item->protection('acid') }}}
      &nbsp;&nbsp;&nbsp;
      防火: {{{  $item->protection('fire') }}}<br>
      环境保护: {{{ $item->environmental_protection }}}<br>
      保暖度: {{{ $item->warmth }}}<br>
      容积: {{{ $item->storage }}}<br>
    @endif

    @if ($item->isBrewable)
      <br>
      {!!$item->brewable!!}
      <br>
    @endif

    @if ($item->isContainer)
    @if ($item->rigid=='R')
      This item is rigid.<br>
    @endif
    @if ($item->seals=='S')
      这个容器能能<info>重新封装</info>。<br>
    @endif
    @if ($item->watertight=='W')
      这个容器是<info>水密</info>的。<br>
    @endif
    @if ($item->preserves=='P')
      这个容器能<good>防止腐坏</good>。<br>
    @endif
      这个容器能储存{{ $item->contains }}升液体。<br>
    @endif

    @if ($item->isBook)
    --<br>
    @if ($item->skill=="none")
    仅供娱乐。<br>
    @else
    可以提升你的 {{ $item->skill }} 技能到 {{ $item->max_level }} 级<br>

    @if ($item->required_level==0)
    适合<info>初学者</info>阅读。.<br>
    @else
    需要 <info>{{ $item->skill }} 技能</info> {{ $item->required_level }} 级才能理解。<br>
    @endif
    @endif
    需要 <info>智力</info> {{ $item->intelligence }} 点才能轻松阅读。<br>
    @if ($item->fun!=0)
    阅读此书对会使你的心情值 {{ $item->fun }}<br>
    @endif
    阅读此书的一个章节需要 {{ $item->time }} <info>分钟</info>。<br>
    @if ($item->chapters)
    章节: {{ $item->chapters }}.<br>
    @endif
    --<br>
    这本书包含 {{ $item->count("learn") }} 个配方:<br>
    {!! $item->craftingRecipes !!}
    @endif
    <br>
    {{{ $item->description }}}<br>
    @if ($item->hasFlag("FIT"))
    <br>这件装备很<info>合身</info>。<br>
    @endif
    @if ($item->hasFlag("OVERSIZE"))
    <br>这件装备尺码足够大，能够容纳下 <info>大型变异肢体</info>。<br>
    @endif
    @if ($item->hasFlag("SKINTIGHT"))
    <br>This piece of clothing lies close to the skin and layers easily.<br>
    @endif
    @if ($item->hasFlag("POCKETS"))
    <br>这件装备有<info>口袋</info>，能在你空手时把手放在口袋里，为手部保暖。<br>
    @endif
    @if ($item->hasFlag("HOOD"))
    <br>这件装备有<info>兜帽</info>，能在头部没有累赘时戴上兜帽，为头部保暖。<br>
    @endif
    @if ($item->hasFlag("RAINPROOF"))
    <br>这件装备能够让你在雨中保持<info>干燥</info>。<br>
    @endif
    @if ($item->hasFlag("SUN_GLASSES"))
    <br>这件装备能<info>防眩光</info>。<br>
    @endif
    @if ($item->hasFlag("WATER_FRIENDLY"))
    <br>这件装备在<info>湿透</info>时依旧<good>性能良好</good>，不受心情值惩罚。<br>
    @endif
    @if ($item->hasFlag("WATERPROOF"))
    <br>这件装备<info>不透水</info>，除非你跳进河里或者被水淹没。<br>
    @endif
    @if ($item->hasFlag("STURDY"))
    <br>这件装备具有<good>良好防护</good>，能使你免于受伤并<info>承受大量伤害</info>。<br>
    @endif
    @if ($item->hasFlag("SWIM_GOGGLES"))
    <br>这件装备能让你在<info>水下</info><good>看得更远</good>。<br>
    @endif
    @if ($item->hasFlag("LEAK_DAM") && $item->hasFlag("RADIOACTIVE"))
    <br>这件物品的外壳已经 <neutral>裂开</neutral> ，露出 <info>不祥的绿光</info>。<br>
    @endif
    @if ($item->hasFlag("LEAK_ALWAYS") && $item->hasFlag("RADIOACTIVE"))
    <br>这件物品 <neutral>发出</neutral> 了 <info>奇异的绿光</info>。<br>
    @endif

    @if ($item->hasVpartlist)
    <br>这件物品可以安装到载具上，作为:<br>
    {!!$item->VpartFor!!}
    @endif
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
    sortList: [[1,0]]
  });
});
document.addEventListener('DOMContentLoaded', (event) => {
  document.querySelectorAll('pre code').forEach((block) => {
    hljs.highlightBlock(block);
  });
});
</script>
