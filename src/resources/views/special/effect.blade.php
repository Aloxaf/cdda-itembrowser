@section('title')
效果 - Cataclysm: Dark Days Ahead
@endsection

@foreach($items as $item)
  <h1> {{ $item->name }} {!!$item->modLabel!!}</h1>

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