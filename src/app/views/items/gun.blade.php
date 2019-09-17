@section('title')
远程武器 - Cataclysm: Dark Days Ahead
@endsection
<h1>远程武器</h1>
<ul class="nav nav-tabs">
@foreach($skills as $value)
<li @if($value==$skill) class="active" @endif><a href="{{ route(Route::currentRouteName(), $value) }}">
{{{
array(
  "archery" => "弓",
  "launcher" => "发射器",
  "melee" => "火器",
  "pistol" => "手枪",
  "rifle" => "步枪",
  "shotgun" => "霰弹枪",
  "smg" => "冲锋枪",
  "throw" => "投掷",
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
    <th>体积(L)</th>
    <th>重量(KG)</th>
    <th>伤害</th>
    <th>射程</th>
    <th>散布</th>
  </tr>
</thead>
<tbody>
@foreach($items as $item)
<tr>
  <td>{{ $item->symbol }}</td>
  <td><a href="{{route('item.view', $item->id)}}">{{ $item->name }} {{ $item->modLabel }}</a></td>
  <td>{{ $item->volume }}</td>
  <td>{{ $item->weightMetric }}</td>
  <td>{{ $item->ranged_damage }}</td>
  <td>{{ $item->range }}</td>
  <td>{{ $item->dispersion }}</td>
</tr>
@endforeach
</tbody>
</table>
<script>
$(function() {
    $(".tablesorter").tablesorter({
      sortList: [[4,1]]
      });
});
</script>
