@section('title')
Consumables - Cataclysm: Dark Days Ahead
@endsection
<style type="text/css">
tr:nth-child(2n) {background-color:#1C1C1C}
</style>
<h1>Consumables</h1>

<ul class="nav nav-tabs">
@foreach($types as $value)
<li @if($value==$type) class="active" @endif><a href="{{ route(Route::currentRouteName(), $value) }}">{{{ucfirst($value)}}}</a></li>
@endforeach
</ul>
<table class="table table-bordered table-hover tablesorter">
  <thead>
  <tr>
    <th></th>
    <th>Name</th>
    <th><span title="Servings">Serv</span></th>
    <th><span title="Quench per Serving">Quen</span></th>
    <th><span title="Nutrition per Serving">Nt</span></th>
    <th><span title="Total Quench">T.Quen</span></th>
    <th><span title="Total Nutrition">T.Nt</span></th>
    <th><span title="Days to spoil">Spoil</span></th>
    <th><span title="Stimulant">Stim</span></th>
    <th><span title="Health">Hea</span></th>
    <th><span title="Addiction">Adi</span></th>
    <th>Fun</th>
  </tr>
</thead>
@foreach($items as $item)
<tr>
  <td>{{ $item->symbol }}</td>
  <td><a href="{{route('item.view', $item->id)}}">{{ $item->name }}</a></td>
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
