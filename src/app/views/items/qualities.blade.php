@section('title')
功能 - Cataclysm: Dark Days Ahead
@endsection
<h1>功能</h1>

<div class="row">
  <div class="col-md-3">
<ul class="nav nav-pills nav-stacked">
@foreach($qualities as $quality)
<li class="@if($quality->id==$id) active @endif"><a href="{{ route(Route::currentRouteName(), $quality->id) }}">{{{$quality->name}}}</a></li>
@endforeach
</ul>
  </div>
  <div class="col-md-9">
@if (!$id)
Please select an entry from the menu on the left.
@else
<table class="table table-bordered table-hover tablesorter">
  <thead>
  <tr>
    <th></th>
    <th>名称</th>
    <th>等级</th>
    <th>配方</th>
    <th>建造</th>
  </tr>
</thead>
@foreach($items as $item)
<tr>
  <td>{{ $item->symbol }}</td>
  <td><a href="{{route('item.view', $item->id)}}">{{ $item->name }} {{ $item->modLabel }}</a></td>
  <td>{{{ $item->qualityLevel($id) }}}</td>
  <td>{{{ $item->count("toolFor") }}}</td>
  <td>{{{ $item->count("construction") }}}</td>
</tr>
</tr>
@endforeach
</table>
<script>
$(function() {
    $(".tablesorter").tablesorter({
      sortList: [[2,0]]
      });
});
</script>
@endif
</div>
</div>
