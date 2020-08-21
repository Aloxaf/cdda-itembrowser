@section('title')
{{{$itembunch[0]->rawName}}} (拆解)) - CDDA 物品浏览器
@endsection
@section('description')
@if ($itembunch[0]->count("disassembly")>0)
{{{$itembunch[0]->rawName}}} 可以被拆解。你可以在此找到更多相关信息。
@else
{{{$itembunch[0]->rawName}}} 无法被拆解。
@endif
@endsection
@include('items.menu', array('active'=>'disassemble'))
<h1>
  {!!$itembunch[0]->symbol!!} <a href="{{ route("item.view", array("id"=>$itembunch[0]->id)) }}">{{ $itembunch[0]->name }}</a>
@if ($itembunch[0]->count("disassembly")>0)
 可以被拆解为下列材料：<br>
@else
 无法被拆解。
@endif
</h1>
<div class="row">
<div class="col-md-8">
@foreach ($itembunch[0]->disassembly as $recipe)
  @if ($recipe->hasTools || $recipe->hasQualities)
  工具需求：<br>
  @if ($recipe->hasQualities)
    {!! $recipe->qualities !!}
  @endif
  @if ($recipe->hasTools)
  {!!$recipe->tools!!}<br>
  @endif
  @endif

  @if ($recipe->hasComponents)
  可获得：<br>
  {!!$recipe->components!!}<br>
  @endif
  --<br>
<br>
@endforeach
  注意：如果可获得材料中有对应多个材料的组，则每组只会获得一个材料。如果这个物品是被制作的，会返回制作时使用的材料，否则则返回第一个材料。<br>
</div>

</div>
