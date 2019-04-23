@section('title')
Monster Groups - Cataclysm: Dark Days Ahead
@endsection
@section('description')
Monster groups
@endsection
<div class="row">
<div class="col-md-3">
<ul class="nav nav-pills nav-stacked">
@foreach($groups as $_group)
<li class="@if ($_group->name==$id) active @endif">{{ link_to_route(Route::currentRouteName(), $_group->name, array($_group->name)) }}</li>
@endforeach
</ul>
</div>
@foreach($groupbunch as $group)
<div class="col-md-9">
@include("monsters/_list", array('data'=>$group->uniqueMonsters))
</div>
@endforeach
</div>
