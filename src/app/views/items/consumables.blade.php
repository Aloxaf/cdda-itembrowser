@section('title')
可消耗物品 - Cataclysm: Dark Days Ahead
@endsection
<style type="text/css">
tr:nth-child(2n) {background-color:#1C1C1C}
</style>
<h1>可消耗物品</h1>

<ul class="nav nav-tabs">
@foreach($types as $value)
<li @if($value==$type) class="active" @endif><a href="{{ route(Route::currentRouteName(), $value) }}">
{{{
array(
    "drink" => "饮料",
    "fermentable" => "发酵品",
    "food" => "食物",
    "med" => "药剂",
    "none" => "其他",
)[$value]
}}}
</a></li>
@endforeach
</ul>
<table class="table table-bordered table-hover tablesorter">
  <thead>
  <tr>
    <th></th>
    <th>名称</th>
    <th>分量</th>
    <th>每份解渴</th>
    <th>每份营养</th>
    <th>解渴</th>
    <th>总营养</th>
    <th>过期时间</th>
    <th>兴奋剂</th>
    <th>健康</th>
    <th>上瘾</th>
    <th>享受</th>
  </tr>
</thead>
@foreach($items as $item)
<tr>
  <td>{{ $item->symbol }}</td>
  <td><a href="{{route('item.view', $item->id)}}">{{ $item->name }} {{ $item->modLabel }}</a></td>
  <td>{{ $item->charges }}</td>
  <td>{{ $item->quench }}</td>
  <td>{{ $item->nutrition }}</td>
  <td>{{ $item->quench * $item->charges }}</td>
  <td>{{ $item->nutrition * $item->charges }}</td>
  <td>{{ $item->spoils_in }}</td>
  <td>{{ $item->stim }}</td>
  <td>{{ $item->healthy }}</td>
  <td>{{ $item->addiction_potential }}</td>
  <td>{{ $item->fun }}</td>
</tr>
</tr>
@endforeach
</table>
<script>
$(function() {
    $(".tablesorter").tablesorter({
@if ($type=="drink")
      sortList: [[3,1]]
@elseif ($type=="food")
      sortList: [[4,1]]
@else
      sortList: [[8,1]]
@endif
      });
});
</script>
