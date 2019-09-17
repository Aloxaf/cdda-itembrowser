@section('title')
装备 - Cataclysm: Dark Days Ahead
@endsection
<h1>装备</h1>
<ul class="nav nav-tabs">
@foreach($parts as $value)
<li @if($value==$part) class="active" @endif><a href="{{ route(Route::currentRouteName(), $value) }}">
{{{
array(
  "arm_either" => "单臂",
  "arms" => "双臂",
  "eyes" => "眼部",
  "feet" => "双脚",
  "foot_either" => "单脚",
  "hand_either" => "单手",
  "hand_r" => "右手",
  "hands" => "双手",
  "head" => "头部",
  "leg_either" => "单腿",
  "legs" => "双腿",
  "mouth" => "嘴巴",
  "torso" => "躯干",
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
    <th>材质</th>
    <th>体积(L)</th>
    <th>重量(KG)</th>
    <th>累赘</th>
    <th>钝击防护></th>
    <th>斩击防护</th>
    <th>保暖</th>
    <th>存储空间(L)</th>
    <th>环境防护</th>
    <th>抗酸</th>
    <th>防火</th>
  </tr>
</thead>
<tbody>
@foreach($items as $item)
<tr>
  <td>{{ $item->symbol }}</td>
  <td><a href="{{route('item.view', $item->id)}}">{{ $item->name }} {{ $item->modLabel }}</a></td>
  <td>{{ $item->materials }}</td>
  <td>{{ $item->volume }}</td>
  <td>{{ $item->weightMetric }}</td>
  <td>{{ $item->encumbrance }}</td>
  <td>{{ $item->protection('bash') }}</td>
  <td>{{ $item->protection('cut') }}</td>
  <td>{{ $item->warmth }}</td>
  <td>{{ $item->storage/4.0 }}</td>
  <td>{{ $item->environmental_protection }}</td>
  <td>{{ $item->protection('acid') }}</td>
  <td>{{ $item->protection('fire') }}</td>
</tr>
@endforeach
</tbody>
</table>
<script>
$(function() {
    $(".tablesorter").tablesorter({
      sortList: [[1,0]]
      });
});
</script>

