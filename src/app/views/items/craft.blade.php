@section('title')
{{{$itembunch[0]->rawName}}} (craft) - Cataclysm: Dark Days Ahead
@endsection
@section('description')
@if ($itembunch[0]->count("recipes")>0)
{{{$itembunch[0]->rawName}}} can be crafted. You can find more information here.
@else
{{{$itembunch[0]->rawName}}} 无法被制作出来。
@endif
@endsection
@include('items.menu', array('active'=>'craft'))
<h1>
  {{$itembunch[0]->symbol}} <a href="{{ route("item.view", array("id"=>$itembunch[0]->id)) }}">{{ $itembunch[0]->name }}</a>
@if ($itembunch[0]->count("recipes")>0)
 可以按下列配方制作：<br>
@else
 无法被制作出来。
@endif
</h1>
<div class="row">
<div class="col-md-6">
@foreach ($itembunch[0]->recipes as $recipe)
  {{ $recipe->labels }}
  主要技能: {{{ $recipe->skill_used }}}({{{ $recipe->difficulty }}})<br>
  其他技能: {{ $recipe->skillsRequired }} <br>
  完成耗时: {{{ $recipe->time }}}<br>
  @if ($recipe->hasTools || $recipe->hasQualities)
  需要工具：<br>
  @if ($recipe->hasQualities)
  @foreach ($recipe->qualities as $q)
  &gt; {{{$q["amount"]}}} 个 <a href="{{ route("item.qualities", $q["quality"]->id) }}">{{{ $q["quality"]->name }}}</a> 功能至少 {{{ $q["level"] }}} 级的工具<br>
  @endforeach
  @endif
  @if ($recipe->hasTools)
  {{$recipe->tools}}<br>
  @endif
  @endif

  @if ($recipe->hasComponents)
  需要材料:<br>
  {{$recipe->components}}<br>
  @endif

  副产品:<br>
  @if ($recipe->hasByproducts)
  {{$recipe->byproducts}}<br>
  @else
  (无)<br>
  @endif
@if ($recipe->canBeLearned)
--<br>
当 <info>{{$recipe->skill_used}}</info> 技能满足最低要求时，这个配方可以于下列书籍中找到：<br>
@foreach($recipe->booksTeaching as $book)
<a href="{{ route('item.view', $book[0]->id) }}">{{{ $book[0]->name }}}
@if ($book[1]<0)
(任何等级)
@else
(等级 {{{ $book[1] }}})
@endif
</a><br>
@endforeach
@endif
<br>
@endforeach
</div>
</div>
