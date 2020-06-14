@section('title')
状态 - Cataclysm: Dark Days Ahead
@endsection

@foreach($items as $item)
  <h1> {{ $item->effect_name }} {!!$item->modLabel!!}</h1>
  @if($item->desc)
    {{ implode(",", $item->desc) }}<br>
  @endif
  --<br>
  @php
    $get = $item->rating === "bad" ? array("<bad>", "</bad>") : array("<good>", "</good>");
    $lose = $item->rating === "bad" ? array("<good>", "</good>") : array("<bad>", "</bad>") ;
  @endphp
  @if($item->apply_message !== NULL)
    获得信息：{!! $get[0].$item->apply_message.$get[1] !!}<br>
  @endif
  @if($item->remove_message !== NULL)
    移除信息：{!! $lose[0].$item->remove_message.$lose[1] !!}<br>
  @endif
  @if (is_array($item->decay_messages))
  衰减信息：<br>
    @foreach ($item->decay_messages as $msg)
      &nbsp;{!! "<{$msg[1]}>$msg[0]</{$msg[1]}>" !!}<br>
    @endforeach
  @endif
  最大层数：{{ $item->max_intensity ?: 1 }}<br>
  --<br>
  @if($item->haskey("removes_effects"))
    移除状态：{!! $item->removes_effects !!}<br>
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