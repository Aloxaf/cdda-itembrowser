@section('title')
Monster Groups - CDDA 物品浏览器
@endsection
@section('description')
Monster groups
@endsection
<div class="row">
<div class="col-md-3">
<ul class="nav nav-pills nav-stacked">
@foreach($groups as $_group)
<li class="@if ($_group->name==$id) active @endif"><a href="{{ route(Route::currentRouteName(), array($_group->name)) }}">{{$_group->name}}</a></li>
@endforeach
</ul>
</div>
@foreach($groupbunch as $group)
<div class="col-md-9">
@include("monsters/_list", array('data'=>$group->uniqueMonsters))
</div>
@endforeach
</div>
