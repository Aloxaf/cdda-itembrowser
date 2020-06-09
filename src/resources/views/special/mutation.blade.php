@section('title')
变异 - Cataclysm: Dark Days Ahead
@endsection

@foreach($muts as $mut)
  <h1>{{ $mut->name }} {!!$mut->modLabel!!}</h1>
  {{ $mut->description }}<br>
  --<br>
  点数：<yellow>{{ $mut->points }}</yellow><br>
  可见性：<yellow>{{ $mut->visibility ?: 0 }}</yellow><br>
  丑陋：<yellow>{{ $mut->ugliness ?: 0 }}</yellow><br>
  --<br>
  @if($mut->hasKey('cut_dmg_bonus'))
    斩击防护：<yellow>{{ $mut->cut_dmg_bonus }}</yellow><br>
  @endif
  @if($mut->hasKey('pierce_dmg_bonus'))
    刺击防护：<yellow>{{ $mut->pierce_dmg_bonus }}</yellow><br>
  @endif
  @if($mut->hasKey('bash_dmg_bonus'))
    钝击防护：<yellow>{{ $mut->bash_dmg_bonus }}</yellow><br>
  @endif
  @if($mut->hasKey('mana_regen_multiplier'))
    魔力回复速率：<yellow>{{ $mut->mana_regen_multiplier * 100 }}</yellow>%<br>
  @endif
  @if($mut->hasKey('mana_modifier'))
    魔力加成：<yellow>{{ $mut->mana_modifier }}</yellow> 单位<br>
  @endif
  @if($mut->hasKey('mana_multiplier'))
    魔力加成：<yellow>{{ $mut->mana_multiplier * 100 }}</yellow>%<br>
  @endif
  --<br>
  @if($mut->hasKey('prereqs'))
    前置1：{!! $mut->mutation_list('prereqs') !!}<br>
  @endif
  @if($mut->hasKey('prereqs2'))
    前置2：{!! $mut->mutation_list('prereqs2') !!}<br>
  @endif
  @if($mut->hasKey('threshreq'))
    阈值：{!! $mut->mutation_list('threshreq') !!}<br>
  @endif
  @if($mut->hasKey('cancels'))
    抵消：{!! $mut->mutation_list('cancels') !!}<br>
  @endif
  @if($mut->hasKey('changes_to'))
    进化为：{!! $mut->mutation_list('changes_to') !!}<br>
  @endif
  @if($mut->hasKey('leads_to'))
    进化增加：{!! $mut->mutation_list('leads_to') !!}<br>
  @endif
  @if($mut->hasKey('category'))
    {{-- Hack --}}
    进化方向：{!! str_replace('mutation', 'mutations', $mut->mutation_list('category')) !!}<br>
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