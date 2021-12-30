<div class="row">
  <div class="col-md-8">
<h1>CDDA 物品浏览器</h1>

<p>
版本: {{{ $version }}}
</p>

<p>
这是一个 <a href="http://cataclysmdda.org">大灾变: 劫后余生</a> 小工具。
通过读取游戏的数据文件并创建一个优化后的数据库，可以将所有内容链接在一起，然后就可以浏览游戏中中的物品和配方。
</p>

<p>
制作从此变得轻松无比。
</p>

<p>
<h2>常用物品</h2>
<a href="{{route('item.recipes', array('id'=>'fire')) }}">生火</a> 总是没有坏处的。
<a href="{{route('item.view', "toolset") }}">瑞士军手</a> 可以用来制造大量物品。
<br>
为了修复你的装备和衣服，你可以切割一些
<a href="{{ route('item.materials', 'wood') }}">木质物品</a>
来获得
<a href="{{ route('item.view', 'splinter') }}">碎木</a>，
然后你就可以
<a href="{{ route('item.craft', 'needle_wood') }}">制作一根木针</a>,
接着你需要
<a href="{{ route('item.view', 'thread') }}">缝衣线</a>
这个可以通过
<a href="{{ route('item.disassemble', 'rag') }}">拆解一块布条</a>
而布条可以通过切割
<a href="{{ route('item.materials', 'cotton') }}">棉质物品</a> 来获得。
<br>
</p>

<h2>变异路线</h2>
<p>
<a href="https://cdda-trunk.aloxaf.cn/mutations.svg" target='_blank'>变异路线图(剧透警告)</a>
<a href="https://cdda-trunk.aloxaf.cn/mutation_threshold.svg" target='_blank'>变异阈值(剧透警告)</a>
<a href="https://cdda-trunk.aloxaf.cn/mutation_conflict.svg" target='_blank'>变异冲突(剧透警告)</a>
<br>
注1：前置1/前置2 - 两组前置突变，满足任意一组才能向下一个方向突变
<br>
注2：阈值 - 使这个突变成为可能需要的阈值
</p>

<h2>文档</h2>
<p>
实验版文档：<a href="https://cdda-trunk.aloxaf.cn/doc">https://cdda-trunk.aloxaf.cn/doc</a>
</p>

<hr>
<p>
原版物品浏览器项目地址: <a href="https://github.com/Sheco/cdda-itembrowser">Github</a>.
<br/>
实验版物品浏览器项目地址: <a href="https://github.com/DanmakuDan/cdda-itembrowser">Github</a>.
<br/>
中文版项目地址: <a href="https://github.com/Aloxaf/cdda-itembrowser">Github</a>.
<br/>
意见反馈请前往：<a href="https://cdda-wiki.aloxaf.cn/%E7%94%A8%E6%88%B7%E8%AE%A8%E8%AE%BA:Aloxaf">我的用户讨论页</a>.
</p>

<hr>

<h2>更新日志</h2>
2021-12-20：<a href="https://cdda-wiki.aloxaf.cn/wiki/分类:法术">法术</a> 已导入 WIKI <br/>
2021-12-17：<a href="https://cdda-wiki.aloxaf.cn">CDDA Wiki</a> 建立了，顺便作为本站的官方讨论及内容补充区 <br/>
2021-12-13 ~ 2021-12-15：作者复活了，迁移了网站，并修复了一堆 503 错误 <br/>

</div>

<div class="col-md-3">
<ul class="nav nav-pills nav-stacked">
<h2>物品分类</h2>

<li><a href="{{ route('item.armors') }}">装备</a></li>
<li><a href="{{ route('item.melee') }}">近战武器</a></li>
<li><a href="{{ route('item.guns') }}">远程武器</a></li>
<li><a href="{{ route('item.consumables') }}">可消耗物品</a></li>
<li><a href="{{ route('item.books') }}">书籍</a></li>
<li><a href="{{ route('item.materials') }}">材料</a></li>
<li><a href="{{ route('item.qualities') }}">功能</a></li>
<li><a href="{{ route('item.containers') }}">容器</a></li>
<li><a href="{{ route('item.flags') }}">Flags</a></li>
<li><a href="{{ route('item.skills') }}">技能</a></li>
<li><a href="{{ route('item.proficiencies') }}">专长</a></li>
<li><a href="{{ route('item.gunmods', array('步枪', '瞄准器')) }}">改装模组</a></li>

<h2>怪物分类</h2>
<li><a href="{{ route('monster.groups') }}">Groups</a></li>
<li><a href="{{ route('monster.species') }}">种族</a></li>
<li><a href="{{ route('monster.flags') }}">Flags</a></li>

<h2>建造</h2>
<li><a href="{{ route('construction.categories') }}">分类</a></li>
<h2>其他</h2>
<li><a href="{{ route('special.mutations') }}">变异</a></li>
<li><a href="{{ route('special.latestchanges') }}">最新变化</a></li>
</ul>
</div>
</div>
