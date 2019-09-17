@section('title')
容器 - Cataclysm: Dark Days Ahead
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
    <th>防止腐坏</th>
    <th>容量(L)</th>
  </tr>
</thead>
@foreach($items as $item)
<tr>
  <td>{{ $item->symbol }}</td>
  <td><a href="{{route('item.view', $item->id)}}">{{ $item->name }} {{ $item->modLabel }}</a></td>
  <td>{{ $item->materials }}</td>
  <td>{{ $item->rigid }}</td>
  <td>{{ $item->seals }}</td>
  <td>{{ $item->watertight }}</td>
  <td>{{ $item->preserves }}</td>
  <td>{{ $item->contains }}</td>
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
