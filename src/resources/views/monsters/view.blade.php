@section('title')
怪物: {{ $monsterbunch[0]->niceName }} - CDDA 物品浏览器
@endsection
@section('description')
怪物: {{ $monsterbunch[0]->niceName }}
@endsection

@foreach($monsterbunch as $monster)
  <div class="row">
    <div class="col-md-8">
      <h4>{!! $monster->symbol !!} {{ $monster->rawname }} {!!$monster->modinfo!!}</h4>
      <p>{{ $monster->description }}</p>
      <br>
      <table>
        <tr>
          <td colspan="2" width="50%"><b>常规</b></td>
          <td colspan="2" width="50%"><b>战斗</b></td>
        </tr>
        <tr>
          <td>HP：</td>
          <td><y>{{ $monster->hp }}</y></td>

          <td>近战技能：</td>
          <td><y>{{ $monster->melee_skill }}</y></td>
        </tr>
        <tr>
          <td>种类：</td>
          <td>{!!$monster->species!!}</td>

          <td>闪避技能：</td>
          <td><y>{{ $monster->dodge }}</y></td>
        </tr>
        <tr>
          <td>速度：</td>
          <td><y>{{ $monster->speed }}</y></td>

          <td>伤害：</td>
          <td><y>{{ $monster->damage }}</y></td>
        </tr>
        <tr>
          <td>材质：</td>
          <td>{{ $monster->material }}</td>

          <td>攻击消耗：</td>
          <td><y>{{ $monster->attack_cost ?: 100 }}</y></td>
        </tr>
        <tr>
          <td>体型：</td>
          <td colspan="2">{!! $monster->size !!}</td>
        </tr>
        <tr>
          <td colspan="2"><br><b>防护</b></td>
          <td colspan="2"><br><b>触发器</b></td>
        </tr>
        <tr>
          <td>钝击防护：</td>
          <td><y>{{ $monster->armor_bash }}</y></td>

          <td>死亡：</td>
          <td>{{ $monster->death_function }}</td>
        </tr>
        <tr>
          <td>斩击防护：</td>
          <td><y>{{ $monster->armor_cut }}</y></td>

          <td valign="top">攻击：</td>
          <td>
            <details>
              <summary>展开</summary>
              {!!$monster->special_attacks!!}
            </details>
          </td>
        </tr>
        <tr>
          <td>防弹：</td>
          <td><y>{{ $monster->armor_bullet }}</y></td>
          <td>击中时：</td>
          <td>{{ $monster->specialWhenHit }}</td>
        </tr>
        <tr>
          <td colspan="4"><br><b>其他</b></td>
        </tr>
        <tr>
          <td>攻击性：</td>
          <td><y>{{ $monster->aggression }}</y></td>
          <td>士气：</td>
          <td><y>{{ $monster->morale }}</y></td>
        </tr>
        <tr>
          <td>难度：</td>
          <td><y>{!!$monster->difficulty!!}</y></td>
          <td>视力：</td>
          <td>{!! "<y>{$monster->vision_day}</y> (夜：<y>{$monster->vision_night}</y>)" !!}</td>
        </tr>
        @if($monster->upgrades)
          <tr>
            @if(array_key_exists("age_grow", $monster->upgrades))
              <td>进化期：</td>
              <td>{{ $monster->upgrades->age_grow }}</td>
            @else
              <td>半数进化期：</td>
              <td><y>{{ $monster->upgrades->half_life ?? 4 }}</y></td>
            @endif
          </tr>
          <tr>
            <td valign="top">进化为：</td>
            <td colspan="3">
              <details>
                <summary>展开</summary>
                {!! $monster->upgrades_to !!}
              </details>
            </td>
          </tr>
        @endif
        <tr>
          @if($monster->harvest != NULL)
            <td valign="top">可收获：</td>
            <td colspan="3"><a href="{{ route("special.itemgroup", $monster->harvest) }}">{{ $monster->harvest }}</a></td>
          @endif
          @if($monster->burn_into != NULL)
            <td>燃烧进化：</td>
            <td><a href="{{ route("monster.view", $monster->burn_into->id) }}">{{ $monster->burn_into->nicename }}</a></td>
          @endif
        </tr>
        <tr>
          <td valign="top">死亡掉落：</td>
          <td colspan="3">{!! $monster->death_drops !!}</td>
        </tr>
      </table>
      <details>
        <summary><b>Flags</b></summary>
        <div class="flag_table">
          @foreach ($monster->flags as $flags)
            {!!'<a href="'.route("monster.flags", $flags[0])."\">* 这个怪物{$flags[1]}</a>" !!}<br>
          @endforeach
        </div>
      </details>
      <br>
      <details>
        <summary>查看 JSON</summary>
        {!!$monster->json!!}
      </details>
    </div>
    <div class="col-md-3">
      @include('layouts.side_ad')
    </div>
  </div>
@endforeach

<script>
  document.addEventListener('DOMContentLoaded', (event) => {
    document.querySelectorAll('pre code').forEach((block) => {
      hljs.highlightBlock(block);
    });
  });
</script>

<style>
  ul {
    padding-left: 15px;
  }

  .flag_table > a {
    color: white;
  }
</style>