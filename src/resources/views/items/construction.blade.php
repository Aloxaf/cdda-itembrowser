@section('title')
{{{$itembunch[0]->rawName}}} (construction) - Cataclysm: Dark Days Ahead
@endsection
@include('items.menu', array('active'=>'construction'))
<h1>
  {!!$itembunch[0]->symbol!!} <a href="{{ route("item.view", array("id"=>$itembunch[0]->id)) }}">{{ $itembunch[0]->name }}</a> construction.
</h1>
@include("world._constructionList", array("data"=>$itembunch[0]->constructionUses))
