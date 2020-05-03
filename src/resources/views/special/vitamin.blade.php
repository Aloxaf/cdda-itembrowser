@section('title')
维生素 - Cataclysm: Dark Days Ahead
@endsection

@foreach($items as $item)
  <h1>{{ $item->rawname }} {!!$item->modLabel!!}</h1>

  类型：{{ $item->vit_type }}<br>

  --<br>
  过量导致：{{ $item->excess }}<br>
  不足导致：{{ $item->deficiency }}<br>
  --<br>
  最小值：<yellow>{{ $item->min }}</yellow> 单位<br>
  最大值：<yellow>{{ $item->max }}</yellow> 单位<br>
  流失速度：<yellow>1</yellow> 单位 / <yellow>{{ $item->rate }}</yellow><br>
  --<br>
  @if ($item->disease !== NULL)
    缺乏阈值：<br>{!!
      implode("<br>", array_map(
        function($v, $k) {
          return "&nbsp;阶段 {$k}：<yellow>{$v[0]}</yellow> ~ <yellow>{$v[1]}</yellow>";
        },
        $item->disease,
        array_keys($item->disease)
      ))
    !!}<br>
  @endif
  @if ($item->disease_excess)
    过量阈值：<br>{!!
      implode("<br>", array_map(
        function($v, $k) {
          return "&nbsp;阶段 {$k}：<yellow>{$v[0]}</yellow> ~ <yellow>{$v[1]}</yellow>";
        },
        $item->disease_excess,
        array_keys($item->disease_excess)
      ))
    !!}<br>
  @endif

  <br>
  <details>
    <summary>查看 JSON</summary>
    {!!$item->json!!}
  </details>

  <script>
    document.addEventListener('DOMContentLoaded', (event) => {
      document.querySelectorAll('pre code').forEach((block) => {
        hljs.highlightBlock(block);
      });
    });
  </script>
@endforeach