@section('title')
{{{$itembunch[0]->rawName}}} (制作)) - CDDA 物品浏览器
@endsection
@section('description')
@if ($itembunch[0]->count("recipes")>0)
{{{$itembunch[0]->rawName}}} 可以被制作出来。你可以在这里找到更多相关信息。
@else
{{{$itembunch[0]->rawName}}} 无法被制作出来。
@endif
@endsection
@include('items.menu', array('active'=>'craft'))
<h1>
  {!!$itembunch[0]->symbol!!} <a href="{{ route("item.view", array("id"=>$itembunch[0]->id)) }}">{{ $itembunch[0]->name }}</a>
@if ($itembunch[0]->count("recipes")>0)
 可以按下列配方制作：<br>
@else
 无法被制作出来。
@endif
</h1>
<div class="row">
<div class="col-md-8">
@foreach ($itembunch[0]->recipes as $recipe)
  {!! $recipe->labels !!}<br>
  主要技能: {{{ $recipe->skill_used }}}（{{{ $recipe->difficulty }}}）<br>
  其他技能: {{ $recipe->skillsRequired }} <br>
  完成耗时: <info>{{{ $recipe->time }}}</info><br>
  @if ($recipe->batch_time_factors)
  批量耗时减少：<info>{{ "{$recipe->batch_time_factors[0]}%（至少 {$recipe->batch_time_factors[1]} 批）" }}</info><br>
  @endif
  @if (is_array($recipe->flags))
    @foreach ($recipe->flags as $flag)
      @if ($flag === "BLIND_EASY")
      暗处制作：<info>简单</info><br>
      @elseif ($flag === "BLIND_HARD")
      暗处制作：<info>困难</info><br>
      @endif
    @endforeach
  @endif
  @if ($recipe->hasTools || $recipe->hasQualities)
  需要工具：<br>
  @if ($recipe->hasQualities)
    {!! $recipe->qualities !!}
  @endif
  @if ($recipe->hasTools)
  {!!$recipe->tools!!}<br>
  @endif
  @endif

  @if ($recipe->hasComponents)
  需要材料:<br>
  {!!$recipe->components!!}<br>
  @endif

  副产品:<br>
  @if ($recipe->hasByproducts)
  {!!$recipe->byproducts!!}<br>
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
