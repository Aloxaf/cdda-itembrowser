<div class="row">
  <div class="col-md-6">
<h1>Item browser</h1>

<p>
Version: {{{ $version }}}
</p>

<p>
这是一个 <a href="http://en.cataclysmdda.com">大灾变: 黑暗之日</a> 小工具。
通过读取游戏的数据文件并创建一个优化后的数据库，可以将所有内容链接在一起，然后就可以浏览游戏中中的物品和配方。
</p>

<p>
制作从此变得轻松无比。
</p>

<p>
<h2>常用物品</h2>
{{link_to_route('item.recipes', '生火', array("id"=>"fire")) }} 总是没有坏处的。
{{link_to_route('item.view', '瑞士军手', "toolset") }} 可以用来制造大量物品。
<br>
为了修复你的装备和衣服，你可以切割一些
{{ link_to_route("item.materials", "木质物品", "wood") }}
来获得
{{ link_to_route("item.view", "碎木", "splinter") }}，
然后你就可以
{{ link_to_route("item.craft", "制作一根木针", "needle_wood") }}，
接着你需要
{{ link_to_route("item.view", "线", "thread") }}，
这个可以通过
{{ link_to_route("item.disassemble", "拆解一块布条", "rag") }} 来获得，
而布条可以通过切割
{{ link_to_route("item.materials", "棉质物品", "cotton") }} 来获得。
<br>
</p>

<h2>There are two copies of the database</h2>
<p>
On the top bar, there are two links, stable and development, each one points to a copy of the database for the latest stable release and a frequently updated git master copy, respectively.
</p>

<hr>
<p>
原版物品浏览器项目地址: <a href="https://github.com/Sheco/cdda-itembrowser">Github</a>.
<br/>
实验版物品浏览器项目地址: <a href="https://github.com/DanmakuDan/cdda-itembrowser">Github</a>.
<br/>
中文版项目地址: <a href="https://github.com/Aloxaf/cdda-itembrowser">Github</a>.
</p>
</div>

<div class="col-md-3">
<ul class="nav nav-pills nav-stacked">
<h2>物品分类</h2>

<li><a href="{{ route('item.armors') }}">衣物</a></li>
<li><a href="{{ route("item.melee") }}">近战武器</a></li>
<li><a href="{{ route('item.guns') }}">远程武器</a></li>
<li><a href="{{ route('item.consumables') }}">可消耗物品</a></li>
<li><a href="{{ route('item.books') }}">书籍</a></li>
<li><a href="{{ route('item.materials') }}">材料</a></li>
<li><a href="{{ route('item.qualities') }}">功能</a></li>
<li><a href="{{ route("item.containers") }}">容器</a></li>
<li><a href="{{ route("item.flags") }}">Flags</a></li>
<li><a href="{{ route("item.skills") }}">技能</a></li>
<li><a href="{{ route("item.gunmods", array("rifle", "sights")) }}">改装模组</a></li>

<h2>怪物分类</h2>
<li>{{ link_to_route('monster.groups', '组') }}</li>
<li>{{ link_to_route('monster.species', '种族') }}</li>

<h2>建造</h2>
<li>{{ link_to_route('construction.categories', 'Categories') }}</li>
</ul>
</div>
</div>
