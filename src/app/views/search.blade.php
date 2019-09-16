@section('title')
搜索结果： {{$search}} - CDDA 物品浏览器
@stop
<h3>搜索: {{ $search }} </h3>
@if (!empty($items))
<h3>匹配物品：</h3>
@foreach ($items as $item)
<div class="row">

<div class="col-md-6">
  {{$item->symbol}} <a href="{{ route("item.view", array("id"=>$item->id)) }}">{{$item->name}}</a>
  {{ $item->featureLabels }}
</div>

</div>
@endforeach
@endif

@if (!empty($monsters))
<h3>匹配怪物：</h3>
<ul class="list-unstyled">
@foreach($monsters as $monster)
  <li>{{$monster->symbol}} {{ link_to_route("monster.view", $monster->name, array($monster->id)) }}
@endforeach
</ul>
@endif

