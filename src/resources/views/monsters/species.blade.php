@section('title')
怪物种类: {{{$id}}} - Cataclysm: Dark Days Ahead
@endsection
@section('description')
怪物种类: {{{$id}}}
@endsection
<div class="row">
<div class="col-md-3">
<ul class="nav nav-pills nav-stacked">
@foreach($species as $s)
<li class="@if ($s==$id) active @endif"><a href="{{ route(Route::currentRouteName(), array($s)) }}">{{
    array(
        "aberration" => "畸变体",
        "amphibian" => "两栖动物",
        "bird" => "鸟",
        "blob" => "变形怪",
        "demon_spider" => "恶魔蜘蛛",
        "dinosaur" => "恐龙",
        "dragon" => "龙",
        "fish" => "鱼",
        "fungus" => "真菌",
        "hallucination" => "幻象",
        "horror" => "恐怖",
        "human" => "人类",
        "insect" => "昆虫",
        "magical_beast" => "魔法怪兽",
        "mammal" => "哺乳动物",
        "mollusk" => "软体动物",
        "mutant" => "变种人",
        "nether" => "神话生物",
        "plant" => "植物",
        "reptile" => "爬虫",
        "robot" => "机器人",
        "spider" => "蜘蛛",
        "unknown" => "未知",
        "worm" => "蠕虫",
        "zombie" => "丧尸",
        "none" => "无",
        "cracker" => "饼干",
        "cookie" => "曲奇饼",
        "chewgum" => "口香糖",
        "gummy" => "软糖",
        "leech_plant" => "吸血植物",
        "lizardfolk" => "蜥蜴人",
        "marshmallow" => "棉花糖",
        "alien" => "外星人",
        "biocrystal" => "晶体生物",
        "wildalien" => "外星野人",
        "uplifted" => "擢升者",
    )[strtolower($s)]
}}</a></li>
@endforeach
</ul>
</div>
<div class="col-md-9">
@include("monsters/_list", array('data'=>$data))
</div>
</div>
