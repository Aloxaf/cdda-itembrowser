@section('title')
怪物种类: {{ $id }} - CDDA 物品浏览器
@endsection
@section('description')
怪物种类: {{ $id }}
@endsection
<div class="row">
  <div class="col-md-3">
    <ul class="nav nav-pills nav-stacked">
      @php
        $trans = array(
        "aberration" => "畸变体",
        "amphibian" => "两栖动物",
        "alien" => "外星人",
        "biocrystal" => "晶体生物",
        "bird" => "鸟",
        "blob" => "变形怪",
        "cracker" => "饼干",
        "chewgum" => "口香糖",
        "cookie" => "曲奇饼",
        "cyborg" => "生化人",
        "demon_spider" => "恶魔蜘蛛",
        "dinosaur" => "恐龙",
        "dragon" => "龙",
        "fish" => "鱼",
        "fungus" => "真菌",
        "goblin" => "哥布林",
        "gummy" => "软糖",
        "hallucination" => "幻象",
        "horror" => "恐怖",
        "human" => "人类",
        "insect" => "昆虫",
        "insect_flying" => "飞虫",
        "leech_plant" => "吸血植物",
        "lizardfolk" => "蜥蜴人",
        "magical_beast" => "魔法巨兽",
        "mammal" => "哺乳动物",
        "marshmallow" => "棉花糖",
        "mollusk" => "软体动物",
        "mutant" => "变种人",
        "nether" => "神话生物",
        "plant" => "植物",
        "reptile" => "爬虫",
        "robot" => "机器人",
        "slime" => "变形怪",
        "spider" => "蜘蛛",
        "uplift" => "擢升者",
        "worm" => "蠕虫",
        "wildalien" => "外星野生生物",
        "zombie" => "丧尸",
        "none" => "无",
        "unknown" => "未知",
        );
      @endphp
      @foreach($species as $s)
        <li class="@if ($s==$id) active @endif"><a href="{{ route(Route::currentRouteName(), array($s)) }}">{{
isset($trans[strtolower($s)]) ? $trans[strtolower($s)] : strtolower($s)
}}</a></li>
      @endforeach
    </ul>
  </div>
  <div class="col-md-9">
    @include("monsters/_list", array('data'=>$data))
  </div>
</div>
