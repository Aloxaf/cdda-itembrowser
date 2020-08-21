@section('title')
Flags - CDDA 物品浏览器
@endsection
<div class="row">
  <div class="col-md-3">
    <ul class="nav nav-pills nav-stacked tsort">
      @foreach($flags as $key=>$flag)
        <li class="@if ($flag==$id) active @endif@"><a href="{{ route(Route::currentRouteName(), $flag) }}">{{ $flag }}</a></li>
      @endforeach
    </ul>
  </div>
  <div class="col-md-9">
    @if(!$id)
      请从左边选择一项。
    @else
      <ul class="list-unstyled">
        @include("monsters/_list", array('data'=>$mons))
      </ul>
    @endif
  </div>
</div>
<script>
  $(function() {
    $(".tsort>li").tsort();
  });
</script>