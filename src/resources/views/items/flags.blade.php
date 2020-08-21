@section('title')
Flags - CDDA 物品浏览器
@endsection
<div class="row">
  <div class="col-md-3">
<ul class="nav nav-pills nav-stacked tsort">
@foreach($flags as $key=>$flag)
<li class="@if ($flag==$id) active @endif@"><a href="{{ route(Route::currentRouteName(), $flag) }}">{{{$flag}}}</a></li>
@endforeach
</ul>
  </div>
  <div class="col-md-9">
@if (!$id)
Please select an entry from the menu on the left.
@else
<ul class="list-unstyled">
@foreach($items as $item)
  <li>{!! $item->symbol !!} <a href="{{route('item.view', $item->id)}}">{{ $item->name }} {!! $item->modLabel !!}</a></li>
@endforeach
</ul>
@endif
</div>
</div>
<script>
$(function() {
  $(".tsort>li").tsort();
});
</script>
