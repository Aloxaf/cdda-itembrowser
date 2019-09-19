@section('title')
怪物: {{{$monsterbunch[0]->name}}} - Cataclysm: Dark Days Ahead
@endsection
@section('description')
怪物: {{{$monsterbunch[0]->name}}}
@endsection

@foreach($monsterbunch as $monster)
<div class="row">
<div class="col-md-8">
<h4>{{ $monster->symbol }} {{$monster->niceName}} {{$monster->modinfo}}</h4>
<p>{{$monster->description}}</p>
怪物 ID: {{{$monster->id}}}
<br>
<br>
<table>
<tr>
  <td colspan="2" width="50%"><b>常规</b></td>
  <td colspan="2" width="50%"><b>战斗</b></td>
</tr>
<tr>
  <td>HP：</td>
  <td>{{{$monster->hp}}}</td>

  <td>近战技能：</td>
  <td>{{{$monster->melee_skill}}}</td>
</tr>
<tr>
  <td>种类：</td>
  <td>{{$monster->species}}</td>

  <td>闪避技能：</td>
  <td>{{{$monster->dodge}}}</td>
</tr>
<tr>
  <td>体型：</td>
  <td>{{$monster->size}}</td>

  <td>伤害：</td>
  <td>{{$monster->damage }}</td>
</tr>
<tr>
  <td>材质：</td>
  <td>{{{ is_array($monster->material) ? implode(", ",$monster->material) : $monster->material }}}</td>
</tr>
<tr>
  <td>速度：</td>
  <td>{{{$monster->speed}}}</td>
</tr>
<tr>
  <td colspan="2"><br><b>防护</b></td>
  <td colspan="2"><br><b>触发器</b></td>
</tr>
<tr>
  <td>钝击防护：</td>
  <td>{{{$monster->armor_bash}}}</td>

  <td>死亡：</td>
  <td>{{{$monster->death_function}}}</td>
</tr>
<tr>
  <td>斩击防护：</td>
  <td>{{{$monster->armor_cut}}}</td>

  <td valign="top">攻击：</td>
  <td>{{$monster->special_attacks}}</td>
</tr>
<tr>
  <td></td>
  <td></td>
  <td>击中时：</td>
  <td>{{{$monster->specialWhenHit}}}</td>
</tr>
<tr>
  <td colspan="4"><br><b>其他</b></td>
</tr>
<tr>
  <td>攻击性：</td>
  <td>{{{$monster->aggression}}}</td>
  <td>士气：</td>
  <td>{{{$monster->morale}}}</td>
</tr>
<tr>
  <td>难度：</td>
  <td>{{$monster->difficulty}}</td>
  <td>视力：</td>
  <td>{{"{$monster->vision_day} (日) / {$monster->vision_night} (夜)"}}</td>
</tr>
<tr>
  <td valign="top">Flags:</td>
  <td colspan="3">{{{$monster->flags}}}</td>
</tr>
</table>
</div>
</div>
@endforeach
