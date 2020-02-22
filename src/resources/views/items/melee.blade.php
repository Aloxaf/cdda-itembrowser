@section('title')
近战武器 - Cataclysm: Dark Days Ahead
@endsection
<h1>近战武器</h1>
<p>
钝击伤害 + 斩击伤害 + 命中加成 > 7 的物品
</p>

<table class="table table-bordered table-hover tablesorter">
  <thead>
  <tr>
    <th></th>
    <th>名称</th>
    <th>材质</th>
    <th>体积(L)</th>
    <th>重量(KG)</th>
    <th><span title="攻击消耗行动点">M/A</span></th>
    <th>钝击伤害</th>
    <th>斩击伤害</th>
    <th>刺击伤害</th>
    <th><span title="伤害/100行动点">DPM</span></th>
    <th>命中加成</th>
  </tr>
</thead>
@foreach($items as $item)
<tr>
  <td>{!! $item->symbol !!}</td>
  <td><a href="{{route('item.view', $item->id)}}">{{ $item->name }} {!! $item->modLabel !!}</a></td>
  <td>{!! $item->materials !!}</td>
  <td>{{ $item->volume }}</td>
  <td>{{ $item->weightMetric }}</td>
  <td>{{ $item->movesPerAttack }}</td>
  <td>{{ $item->bashing }}</td>
  <td>{{ $item->cutting }}</td>
  <td>{{ $item->piercing }}</td>
  <td>{{ $item->damagePerMove }}</td>
  <td>{{ $item->to_hit }}</td>
</tr>
</tr>
@endforeach
</table>
<script>
$(function() {
    $(".tablesorter").tablesorter({
      sortList: [[7,1]]
      });
});
</script>
