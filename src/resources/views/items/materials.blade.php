@section('title')
Materials - Cataclysm: Dark Days Ahead
@endsection
<h1>Materials</h1>

<div class="row">
  <div class="col-md-3">
<ul class="nav nav-pills nav-stacked">
@foreach($materials as $material)
<li class="@if($material->id==$id) active @endif"><a href="{{ route(Route::currentRouteName(), $material->id) }}">{{{$material->name}}}</a></li>
@endforeach
</ul>
  </div>

  <div class="col-md-9">
@if (!$id)
Please select an entry from the menu on the left.
@else
<table class="table table-bordered table-hover tablesorter">
  <thead>
  <tr>
    <th></th>
    <th>Name</th>
    <th>Volume</th>
    <th>Weight</th>
  </tr>
</thead>
@foreach($items as $item)
<tr>
  <td>{!! $item->symbol !!}</td>
  <td><a href="{{route('item.view', $item->id)}}">{{ $item->name }} {!! $item->modLabel !!}</a></td>
  <td>{{{ $item->volume }}}</td>
  <td>{{{ $item->weight }}}</td>
</tr>
</tr>
@endforeach
</table>
<script>
$(function() {
    $(".tablesorter").tablesorter({
      sortList: [[1,0]]
      });
});
</script>
@endif
</div>
</div>
