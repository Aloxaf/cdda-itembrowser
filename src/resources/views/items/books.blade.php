@section('title')
书籍 - CDDA 物品浏览器
@endsection
<h1>书籍</h1>

<ul class="nav nav-tabs">
@foreach($types as $value)
<li @if($value==$type) class="active" @endif><a href="{{ route(Route::currentRouteName(), $value) }}">{{{ucfirst($value)}}}</a></li>
@endforeach
</ul>
<table class="table table-bordered table-hover tablesorter">
  <thead>
  <tr>
    <th></th>
    <th>名称</th>
    <th>技能</th>
    <th><span title="阅读需求">RL</span></th>
    <th><span title="最大等级">ML</span></th>
    <th>阅读耗时</th>
    <th>心情</th>
    <th>配方</th>
  </tr>
</thead>
@foreach($items as $item)
<tr>
  <td>{!! $item->symbol !!}</td>
  <td><a href="{{route('item.view', $item->id)}}">{{ $item->name }} {!! $item->modLabel !!}</a></td>
  <td>{{ $item->skill }}</td>
  <td>{{ $item->required_level }}</td>
  <td>{{ $item->max_level }}</td>
  <td>{{ $item->time }}</td>
  <td>{{ $item->fun }}</td>
  <td>{{ $item->count("learn") }}</td>
</tr>
</tr>
@endforeach
</table>
<script>
$(function() {
    $(".tablesorter").tablesorter({
      sortList: [[2,0],[3,0],[4,0],[1,0]]
      });
});
</script>
