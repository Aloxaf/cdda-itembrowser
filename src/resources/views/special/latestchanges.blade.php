@section('title')
最新变化 - Cataclysm: Dark Days Ahead
@endsection
<h1>最新变化</h1>

<table class="table table-bordered table-hover tablesorter">
  <thead>
    <tr>
      <th>操作</th>
      <th>名称</th>
      <th>类别</th>
    </tr>
  </thead>
  <tbody>
    @foreach($diff as $item)
      <tr>
        <td>{!! $item->op == "add" ? "<good>增加</good>" : "<bad>删除</bad>" !!}</td>
        @if($item->op == "add")
          @if($item->type == "MONSTER")
            <td><a href="{{ route("monster.view", $item->id) }}">{{ $item->name }}</a></td>
          @elseif($item->type == "material")
            <td><a href="{{ route("item.view", $item->id) }}">{{ $item->name }}</a></td>
          @elseif($item->type == "vehicle_part")
            <td><a href="{{ route("item.view", "vpart_{$item->id}") }}">{{ $item->name }}</a></td>
          @else
            <td><a href="{{ route("item.view", $item->id) }}">{{ $item->name }}</a></td>
          @endif
        @else
          <td>{{ gettext($item->name) }}</td>
        @endif
        <td>{{ $item->type }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

<script>
  $(function() {
    $(".tablesorter").tablesorter();
  });
</script>