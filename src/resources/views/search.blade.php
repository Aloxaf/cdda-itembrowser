@section('title')
Searching for {{$search}} - Cataclysm: Dark Days Ahead Item Browser
@stop
<h3>Search: {{ $search }} </h3>
@if (!empty($items))
<h3>Item matches:</h3>
@foreach ($items as $item)
<div class="row">

<div class="col-md-6">
  {!!$item->symbol!!} <a href="{{ route("item.view", array("id"=>$item->id)) }}">{{$item->name}}</a>
  {!! $item->featureLabels !!}
</div>

</div>
@endforeach
@endif

@if (!empty($monsters))
<h3>Monster matches:</h3>
<ul class="list-unstyled">
@foreach($monsters as $monster)
  <li>{!!$monster->symbol!!} <a href="{{ route('monster.view', array($monster->id)) }}">{{is_object($monster->name)?(isset($monster->name->str)?$monster->name->str:$monster->name->str_sp):$monster->name}}</a>
@endforeach
</ul>
@endif

