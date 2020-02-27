@section("title")
Construction: Cataclysm Dark Days Ahead
@stop
<h3>{{$data->description}}
@if ($data->comment!="")
({{$data->comment}})
@endif
</h3>
类别: <a href="{{ route('construction.categories', $data->category) }}">{{ $data->category }}</a><br>
Difficulty: {{$data->difficulty}} ({{$data->skill}})<br>
耗时: {{$data->time}} 分钟<br>
@if ($data->has_pre_terrain)
需要地形: {!!$data->pre_terrain->symbol!!} {{$data->pre_terrain->name}}<br>
@endif
@if ($data->pre_flags)
Required flags: {{is_array($data->pre_flags) ? implode(", ", $data->pre_flags) : $data->pre_flags}}<br>
@endif
@if ($data->has_post_terrain)
产品: {!!$data->post_terrain->symbol!!} {{$data->post_terrain->name}}<br>
@endif
@if ($data->requiresQualities)
需要工具:<br>
{!!$data->qualities!!}<br>
@if ($data->requiresTools)
{!!$data->tools!!}<br>
@endif
@endif
@if ($data->requiresComponents)
需要材料:<br>
{!!$data->components!!}<br>
@endif

<br>
<details>
  <summary>查看 JSON</summary>
  {!!$data->json!!}
</details>
</div>

<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        document.querySelectorAll('pre code').forEach((block) => {
        hljs.highlightBlock(block);
        });
    });
</script>