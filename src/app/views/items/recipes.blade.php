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
  {{$itembunch[0]->symbol}} <a href="{{ route("item.view", array("id"=>$itembunch[0]->id)) }}">{{ $itembunch[0]->name }}</a>
@if ($itembunch[0]->count("toolFor"))
 can be used to craft the following recipes:<br>
@else
 can't be used to craft anything.
@endif
</h1>
<ul class="nav nav-tabs">
@foreach ($categories as $cat)
<li @if ($cat==$category) class="active" @endif>{{ link_to_route("item.recipes", substr($cat, 3),
      array("id"=>$itembunch[0]->id, "category"=>$cat)) }}</li>
@endforeach
</ul>

<div class="row">
  <div class="col-md-4">
@foreach ($recipes as $recipe_id=>$local_recipe)
{{ $local_recipe->result->symbol }} <a href="#" onclick="return show_recipe('{{$recipe_id}}')">{{{ $local_recipe->result->name }}} {{ $local_recipe->modLabel }} {{ $local_recipe->npcLabel }} {{ $local_recipe->obsoleteLabel }} </a>
<br>
@endforeach
<hr>
  </div>
<div class="col-md-6">
@foreach($recipes as $recipe_id=>$recipe)
<div id="recipe{{$recipe_id}}" class="recipes" style="display: none;">
{{$recipe->result->symbol}} {{ link_to_route("item.view",
$recipe->result->name,
array("id"=>$recipe->result->id)) }}<br>
  {{ $recipe->labels }}
  Category: {{{ $recipe->category }}}<br>
  SubCategory: {{{ $recipe->subcategory }}}<br>
  Primary skill used: {{{ $recipe->skill_used }}}({{{ $recipe->difficulty }}})<br>
  Required skills: {{ $recipe->skillsRequired }} <br>
  Time to complete: {{{ $recipe->time }}}<br>
  Auto-learn: {{{ $recipe->autolearn? "Yes": "No" }}}<br>
  <br>

  @if ($recipe->hasTools || $recipe->hasQualities)
  Tools required:<br>
  @if ($recipe->hasQualities)
  @foreach ($recipe->qualities as $q)
  &gt; {{{$q["amount"]}}} tool with <a href="{{ route("item.qualities", $q["quality"]->id) }}">{{{ $q["quality"]->name }}}</a> quality of {{{ $q["level"] }}}<br>
  @endforeach
  @endif
  @if ($recipe->hasTools)
  {{$recipe->tools}}<br>
  @endif
  @endif

  @if ($recipe->hasComponents)
  Components required:<br>
  {{$recipe->components}}<br>
  @endif

  Byproducts:<br>
  @if ($recipe->hasByproducts)
  {{$recipe->byproducts}}<br>
  @else
  (none)<br>
  @endif
</div>
@endforeach
</div>
</div>
