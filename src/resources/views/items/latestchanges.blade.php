@section('title')
最新物品 - Cataclysm: Dark Days Ahead
@endsection
<h1>最新物品</h1>

<table class="table table-bordered table-hover tablesorter">
  <thead>
    <tr>
      <th></th>
      <th>名称</th>
      <th>类别</th>
      <th>材质</th>
    </tr>
  </thead>
  <tbody>
    @foreach($items as $item)
      <tr>
        <td>{!! $item->symbol !!}</td>
        <td><a href="{{ route("item.view", $item->id) }}">{{ $item->name }}</a></td>
        <td>{{ $item->type }}</td>
        <td>{!! $item->materials !!}</td>
      </tr>
    @endforeach
  </tbody>
</table>

<script>
  $(function() {
    $(".tablesorter").tablesorter();
  });
</script>