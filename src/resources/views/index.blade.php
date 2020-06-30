<div class="row">
  <div class="col-md-6">
<h1>Item browser</h1>

<p>
Version: {{{ $version }}}
</p>

<p>
This is a simple tool to browse through the items and recipes available in <a href="http://cataclysmdda.org/">Cataclysm: Dark Days Ahead</a>,
this is done by reading the game's data files and creating an optimized database linking everything together.
</p>

<p>
Crafting could be as simple as looking at your hammer and being able to know what you can do with it.
</p>

<p>
<h2>Common useful items</h2>
It's always nice to make a <a href="{{route('item.recipes', array('id'=>'fire')) }}">fire</a>. There are lots of things you can do with an <a href="{{route('item.view', "toolset") }}">integrated toolset</a>.<br>
<br>
To repair your armor and clothes, you can cut some
<a href="{{ route('item.materials', 'wood') }}">items made of wood</a>
 to obtain
 <a href="{{ route('item.view', 'skewer') }}">skewers</a>,
 with that you can
 <a href="{{ route('item.craft', 'needle_wood') }}">craft a wooden needle</a>,
 then you need
 <a href="{{ route('item.view', 'thread') }}">thread</a>
 so you will have to
 <a href="{{ route('item.disassemble', 'rag') }}">disassemble a rag</a>,
 which can be obtained by cutting
 <a href="{{ route('item.materials', 'cotton') }}">items made of cotton</a>.
<br>
</p>

<h2>There are two copies of the database</h2>
<p>
On the top bar, there are two links, stable and development, each one points to a copy of the database for the latest stable release and a frequently updated git master copy, respectively.
</p>

<hr>
<p>
The source code for the original item browser is available at <a href="https://github.com/Sheco/cdda-itembrowser">Github</a>.
<br/>
The source code for the latest experimental item browser is also available at <a href="https://github.com/DanmakuDan/cdda-itembrowser">Github</a>.
</p>
</div>

<div class="col-md-3">
<ul class="nav nav-pills nav-stacked">
<h2>Item catalogs</h2>

<li><a href="{{ route('item.armors') }}">Clothing</a></li>
<li><a href="{{ route('item.melee') }}">Melee</a></li>
<li><a href="{{ route('item.guns') }}">Ranged weapons</a></li>
<li><a href="{{ route('item.consumables') }}">Consumables</a></li>
<li><a href="{{ route('item.books') }}">Books</a></li>
<li><a href="{{ route('item.materials') }}">Materials</a></li>
<li><a href="{{ route('item.qualities') }}">Qualities</a></li>
<li><a href="{{ route('item.containers') }}">Containers</a></li>
<li><a href="{{ route('item.flags') }}">Flags</a></li>
<li><a href="{{ route('item.skills') }}">Skills</a></li>
<li><a href="{{ route('item.gunmods', array('rifle', 'sights')) }}">Gun mods</a></li>

<h2>Monster catalogs</h2>
<li><a href="{{ route('monster.groups') }}">Groups</a></li>
<li><a href="{{ route('monster.species') }}">Species</a></li>

<h2>Construction</h2>
<li><a href="{{ route('construction.categories') }}">Categories</a></li>
</ul>
</div>
</div>
