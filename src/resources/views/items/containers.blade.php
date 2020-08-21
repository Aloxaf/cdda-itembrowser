@section('title')
容器 - CDDA 物品浏览器
@endsection
<h1>容器</h1>

<table class="table table-bordered table-hover tablesorter">
  <thead>
    <tr>
      <th></th>
      <th>名称</th>
      <th>材料</th>
      <th>柔性</th>
      <th>可重封</th>
      <th>水密</th>
      <th>腐烂速度</th>
      <th>容量(L)</th>
    </tr>
  </thead>
  @foreach($items as $item)
    @php
      $pocket_data = $item->pocket_data[0];
    @endphp
    <tr>
      <td>{!! $item->symbol !!}</td>
      <td><a href="{{ route('item.view', $item->id) }}">{{ $item->name }} {!! $item->modLabel !!}</a></td>
      <td>{!! $item->materials !!}</td>
      <td>{{ ($pocket_data->rigid ?? true) ? "是" : "否" }}</td>
      <td>{{ isset($pocket_data->sealed_data) ? "否" : "是" }}</td>
      <td>{{ ($pocket_data->watertight ?? false) ? "是" : "否" }}</td>
      <td>{{ $pocket_data->spoil_multiplier ?? 1.0 }}</td>
      <td>{{ $item->storage }}</td>
    </tr>
    </tr>
  @endforeach
</table>
<script>
  $(function() {
    $(".tablesorter").tablesorter({
      sortList: [
        [7, 1]
      ]
    });
  });
</script>