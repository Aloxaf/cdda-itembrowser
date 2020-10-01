@section('title')
变异 - CDDA 物品浏览器
@endsection

@foreach($muts as $mut)
  <h1>{{ $mut->name }} {!!$mut->modLabel!!}</h1>
  {{ $mut->description }}<br>
  --<br>
  <table>
    <tr>
      <td colspan="3" width="200px"><b>常规</b></td>
    </tr>
    <tr>
      <td>点数：</td>
      <td><yellow>{{ $mut->points }}</yellow></td>
      <td>可净化：</td>
      <td>{{ ($mut->purifiable ?? true) ? "是" : "否" }}</td>
    </tr>
    <tr>
      <td>可见性：</td>
      <td><yellow>{{ $mut->visibility ?: 0 }}</yellow></td>
    </tr>
    <tr>
      <td>丑陋：</td>
      <td><yellow>{{ sprintf("%+d", $mut->ugliness ?: 0) }}</yellow></td>
    </tr>
  </table>
  <table>
    <tr>
      <td colspan="4" width="80%"><br><b>加成</b></td>
    </tr>
    <tr>
      <td>社交加成：</td>
      <td>
        @if($mut->hasKey('social_modifiers'))
          @php
            $mods = array(
              "lie" => "撒谎",
              "persuade" => "说服",
              "intimidate" => "威胁"
            );
          @endphp
          @foreach($mut->social_modifiers as $mod => $value)
            {!! "{$mods[$mod]} <y>".sprintf("%+d", $value)."</y> &nbsp;" !!}
          @endforeach
        @else
          无
        @endif
      </td>
      <td>属性加成：</td>
      <td>
        @if($mut->hasKey('passive_mods'))
          @php
            $mods = array(
              "per_mod" => "感知",
              "str_mod" => "力量",
              "dex_mod" => "敏捷",
              "int_mod" => "智力"
            );
          @endphp
          @foreach($mut->passive_mods as $mod => $value)
            {!! "{$mods[$mod]} <y>".sprintf("%+d", $value)."</y> &nbsp;" !!}
          @endforeach
        @else
          无
        @endif
      </td>
      <td>闪避加成：</td>
      <td>
        @if($mut->hasKey('dodge_modifier'))
          <y>{{ sprintf("%+d", $mut->dodge_modifier) }}</y><br>
        @else
          无
        @endif
      </td>
    </tr>
    <tr>
      <td>斩击加成</td>
      <td>
        @if ($mut->cut_dmg_bonus !== NULL)
          {{ $mut->cut_dmg_bonus }}
        @elseif ($mut->rand_cut_bonus !== NULL)
          {{ "{$mut->rand_cut_bonus->min} ~ {$mut->rand_cut_bonus->max}" }}
        @endif
      </td>
      <td>钝击加成</td>
      <td>
        @if ($mut->bash_dmg_bonus)
          {{ $mut->bash_dmg_bonus }}
        @elseif ($mut->rand_bash_bonus !== NULL)
          {{ "{$mut->rand_bash_bonus->min} ~ {$mut->rand_bash_bonus->max}" }}
        @endif
      </td>
      <td>刺击加成</td>
      <td>
        @if ($mut->pierce_dmg_bonus)
          {{ $mut->pierce_dmg_bonus }}
        @endif
      </td>
    </tr>
  </table>
  <table>
    <tr>
      <td colspan="6" width="80%"><br><b>变异</b></td>
    </tr>
    <tr>
      <td>前置1：</td>
      <td>
        @if($mut->hasKey('prereqs'))
          {!! $mut->mutation_list('prereqs') !!}
        @else
          无
        @endif
      </td>
      <td>前置2：</td>
      <td>
        @if($mut->hasKey('prereqs2'))
          {!! $mut->mutation_list('prereqs2') !!}
        @else
          无
        @endif
      </td>
    </tr>
    <tr>
      <td>阈值：</td>
      <td>
        @if($mut->hasKey('threshreq'))
          {!! $mut->mutation_list('threshreq') !!}
        @else
          无
        @endif
      </td>
      <td>进化方向：</td>
      <td>
        @if($mut->hasKey('category'))
          {{-- Hack --}}
          {!! str_replace('mutation', 'mutations', $mut->mutation_list('category')) !!}
        @else
          无
        @endif
      </td>
    </tr>
    <tr>
      <td>冲突：</td>
      <td>
        @if($mut->hasKey('cancels'))
          {!! $mut->mutation_list('cancels') !!}
        @else
          无
        @endif
      </td>
      @if($mut->hasKey('changes_to'))
        <td>进化为：</td>
        <td>{!! $mut->mutation_list('changes_to') !!}</td>
      @endif
      @if($mut->hasKey('leads_to'))
        <td>进化增加：</td>
        <td>{!! $mut->mutation_list('leads_to') !!}</td>
      @endif
    </tr>
  </table>
  <br>
  @if($mut->hasKey("armor") || $mut->hasKey("wet_protection") ||
    $mut->hasKey("encumbrance_covered") || $mut->hasKey("encumbrance_always"))
    <b>防护</b><br>
    <table>
      <thead>
        <tr>
          <th style="width: 4em">位置</th>
          <th style="width: 7em">累赘（衣物）</th>
          <th style="width: 7em">累赘（永久）</th>
          <th style="width: 4em">斩击</th>
          <th style="width: 4em">钝击</th>
          <th style="width: 4em">子弹</th>
          <th style="width: 7em">湿身（正面）</th>
          <th style="width: 7em">湿身（负面）</th>
        </tr>
      </thead>
      @foreach($mut->all_armor as $armor)
        <tr>
          <td>{{ $armor["parts"]->name }}</td>
          <td>{{ $armor["encumbrance_covered"] ?? 0 }}</td>
          <td>{{ $armor["encumbrance_always"] ?? 0 }}</td>
          <td>{{ $armor["cut"] ?? 0 }}</td>
          <td>{{ $armor["bash"] ?? 0 }}</td>
          <td>{{ $armor["bullet"] ?? 0 }}</td>
          <td>{{ $armor["good"] ?? 0 }}</td>
          <td>{{ $armor["neutral"] ?? 0 }}</td>
        </tr>
      @endforeach
    </table>
  @endif
  --<br>
  @if($mut->hasKey('mana_regen_multiplier'))
    魔力回复速率：<yellow>{{ $mut->mana_regen_multiplier * 100 }}</yellow>%<br>
  @endif
  @if($mut->hasKey('mana_modifier'))
    魔力加成：<yellow>{{ $mut->mana_modifier }}</yellow> 单位<br>
  @endif
  @if($mut->hasKey('mana_multiplier'))
    魔力加成：<yellow>{{ $mut->mana_multiplier * 100 }}</yellow>%<br>
  @endif
  <br>
  <details>
    <summary>查看 JSON</summary>
    {!!$mut->json!!}
  </details>

  <script>
    document.addEventListener('DOMContentLoaded', (event) => {
      document.querySelectorAll('pre code').forEach((block) => {
        hljs.highlightBlock(block);
      });
    });
  </script>
@endforeach