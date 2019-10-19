@section('title')
Melee - Cataclysm: Dark Days Ahead
@endsection
<h1>Melee</h1>
<p>
Items with bashing damage + cutting damage + to-hit bonus higher than 7
</p>

<table class="table table-bordered table-hover tablesorter">
  <thead>
  <tr>
    <th></th>
    <th>Name</th>
    <th>Material</th>
    <th><span title="Volume">V</span></th>
    <th><span title="Weight in pounds">W lbs</span></th>
    <th><span title="Moves per attack">M/A</span></th>
    <th><span title="Bashing damage">Bash</span></th>
    <th><span title="Cutting damage">Cut</span></th>
    <th><span title="Piercing damage">Pierce</span></th>
    <th><span title="Damage per 100 moves">dpm</span></th>
    <th><span title="To-Hit">H</span></th>
  </tr>
</thead>
@foreach($items as $item)
<tr>
  <td>{!! $item->symbol !!}</td>
  <td><a href="{{route('item.view', $item->id)}}">{{ $item->name }} {!! $item->modLabel !!}</a></td>
  <td>{!! $item->materials !!}</td>
  <td>{{ $item->volume }}</td>
  <td>{{ $item->weight }}</td>
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
