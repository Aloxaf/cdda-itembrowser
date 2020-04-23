@section('title')
物品组 - Cataclysm: Dark Days Ahead
@endsection
<h1>物品组</h1>
<ul class="nav nav-tabs">
@foreach ($groups as $group)
  <h1>{{ $group->id }} {!! $group->modinfo !!}</h1>
  @if ($group->type == "item_group")
    @if ($group->ammo)
      <yellow>{{ $group->ammo }}</yellow>% 的几率和弹药一起掉落<br>
    @endif
    @if ($group->magazine)
      <yellow>{{ $group->magazine }}</yellow>% 的几率和弹匣一起掉落<br>
    @endif
    <br>
    {!! $group->items !!}
    <br>
  @elseif ($group->type == "harvest")
    @foreach ($group->harvest as $harvest)
      收获
      @if ($harvest->mass_ratio)
        质量占比 <yellow>{{ $harvest->mass_ratio * 100.0 }}</yellow>% 的
      @endif
      @if (strpos($harvest->type, "_group") != false)
        <a href="{{ route("item.itemgroup", $harvest->id)}}">{{ $harvest->id }}</a><br>
      @else
        <a href="{{ route("item.view", $harvest->id)}}">{{ $harvest->fullname }}</a><br>
    @endif
  @endforeach
@endif

<details>
  <summary>查看 JSON</summary>
  {!! $group->json !!}
</details>
<br>
@endforeach
</tbody>
</table>
<script>
  document.addEventListener('DOMContentLoaded', (event) => {
    document.querySelectorAll('pre code').forEach((block) => {
      hljs.highlightBlock(block);
    });
  });
</script>
<style>
  ul {
    padding-left: 15px;
  }
</style>