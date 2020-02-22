@section('title')
{{{$itembunch[0]->rawName}}} (recipes) - Cataclysm: Dark Days Ahead
@endsection
@section('description')
@if ($itembunch[0]->count("toolFor"))
{{{$itembunch[0]->rawName}}} can be used to craft other items. You can find more information here.
@else
{{{$itembunch[0]->rawName}}} can't be used to craft other items.
@endif
@endsection
<script>
var show_recipe = function(id)
{
  $('.recipes').hide();
  $('#recipe'+id).show();
  var body = $("body");
  $('hmtl, body').animate({
    scrollTop: $("#recipe"+id).offset().top-$(".navbar").height()
  }, 500);
  return false;
}
</script>
@include('items.menu', array('active'=>'recipes'))
<h1>
  {!!$itembunch[0]->symbol!!} <a href="{{ route("item.view", array("id"=>$itembunch[0]->id)) }}">{{ $itembunch[0]->name }}</a>
@if ($itembunch[0]->count("toolFor"))
 可以用于制造下列物品：<br>
@else
 无法被用来制造任何东西。
@endif
</h1>
<ul class="nav nav-tabs">
@foreach ($categories as $cat)
<li @if ($cat==$category) class="active" @endif><a href="{{ route('item.recipes',
      array('id'=>$itembunch[0]->id, 'category'=>$cat)) }}">{{substr($cat, 3)}}</a></li>
@endforeach
</ul>

<div class="row">
  <div class="col-md-4">
@foreach ($recipes as $recipe_id=>$local_recipe)
{!! $local_recipe->result->symbol !!} <a href="#" onclick="return show_recipe('{{$recipe_id}}')">{{{ $local_recipe->result->name }}} {!! $local_recipe->modLabel !!} {{ $local_recipe->npcLabel }} {{ $local_recipe->obsoleteLabel }} </a>
<br>
@endforeach
<hr>
  </div>
<div class="col-md-6">
@foreach($recipes as $recipe_id=>$recipe)
<div id="recipe{{$recipe_id}}" class="recipes" style="display: none;">
{!!$recipe->result->symbol!!} <a href="{{ route('item.view',
array('id'=>$recipe->result->id)) }}">{{$recipe->result->name}}</a><br>
  {!! $recipe->labels !!}
  类别: {{{ $recipe->category }}}<br>
  子类别: {{{ $recipe->subcategory }}}<br>
  主要技能: {{{ $recipe->skill_used }}}({{{ $recipe->difficulty }}})<br>
  其他技能: {{ $recipe->skillsRequired }} <br>
  完成耗时: {{{ $recipe->time }}}<br>
  自动学会: {{{ $recipe->autolearn? "是": "否" }}}<br>
  <br>

  @if ($recipe->hasTools || $recipe->hasQualities)
  需要工具:<br>
  @if ($recipe->hasQualities)
  @foreach ($recipe->qualities as $q)
  &gt; {{{$q["amount"]}}} 个 <a href="{{ route("item.qualities", $q["quality"]->id) }}">{{{ $q["quality"]->name }}}</a> 功能至少 {{{ $q["level"] }}} 级的工具<br>
  @endforeach
  @endif
  @if ($recipe->hasTools)
  {!!$recipe->tools!!}<br>
  @endif
  @endif

  @if ($recipe->hasComponents)
  需要材料：<br>
  {!!$recipe->components!!}<br>
  @endif

  副产品:<br>
  @if ($recipe->hasByproducts)
  {!!$recipe->byproducts!!}<br>
  @else
  (none)<br>
  @endif
</div>
@endforeach
</div>
</div>
