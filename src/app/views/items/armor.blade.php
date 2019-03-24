@section('title')
Armor - Cataclysm: Dark Days Ahead
@endsection
<h1>Armor</h1>
<ul class="nav nav-tabs">
@foreach($parts as $value)
<li @if($value==$part) class="active" @endif><a href="{{ route(Route::currentRouteName(), $value) }}">{{{ucfirst($value)}}}</a></li>
@endforeach
</ul>
<table class="table table-bordered table-hover tablesorter">
  <thead>
  <tr>
    <th></th>
    <th>Name</th>
    <th>Material</th>
    <th><span title="Volume">V</span></th>
    <th><span title="Weight">W</span></th>
    <th><span title="Encumbrance">E</span></th>
    <th><span title="Bash protection">BP</span></th>
    <th><span title="Cutting protection">CP</span></th>
    <th><span title="Warmth">Wa</span></th>
    <th><span title="Storage (liters)">St</span></th>
    <th><span title="Environmental protection">Env</span></th>
    <th><span title="Acid resist">Acid</span></th>
    <th><span title="Fire resist">Fire</span></th>
  </tr>
</thead>
<tbody>
@foreach($items as $item)
<tr>
  <td>{{ $item->symbol }}</td>
  <td><a href="{{route('item.view', $item->id)}}">{{ $item->name }}</a></td>
  <td>{{ $item->materials }}</td>
  <td>{{ $item->volume }}</td>
  <td>{{ $item->weight }}</td>
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

