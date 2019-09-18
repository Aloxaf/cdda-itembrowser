<table class="table table-bordered tablesorter">
<thead>
<tr>
  <th></th>
  <th>名称</th>
  <th>HP</th>
  <th>攻击</th>
  <th>最大攻击</th>
  <th>平均攻击</th>
  <th>近战技能</th>
  <th>闪避技能</th>
</tr>
</thead>
@foreach ($data as $monster)
<tr>
  <td>{{ $monster->symbol }}</td>
  <td><a href="{{ route('monster.view', array($monster->id)) }}">{{ $monster->niceName }} {{ $monster->modinfo }}</a></td>
  <td class="text-right">{{{ $monster->hp }}}</td>
  <td class="text-right">{{{ $monster->damage }}}</td>
  <td class="text-right">{{{ $monster->maxDamage }}}</td>
  <td class="text-right">{{{ $monster->avgDamage }}}</td>
  <td class="text-right">{{{ $monster->melee_skill }}}</td>
  <td class="text-right">{{{ $monster->dodge }}}</td>
</tr>
@endforeach
</table>
<script>
$(function() {
      $(".tablesorter").tablesorter({
            sortList: [[1,0]]
                    });
});
</script>
