@section('title')
Flags - Cataclysm: Dark Days Ahead
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
      Please select an entry from the menu on the left.
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