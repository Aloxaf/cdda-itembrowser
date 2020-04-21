<table class="table table-bordered">
<thead>
<tr>
    <th>建造</th>
    <th></th>
    <th>原料</th>
    <th></th>
    <th>结果</th>
</tr>
</thead>
@foreach($data as $d) 
<tr>
    <td><a href="{{ route('construction.view', $d->repo_id) }}">{{$d->description}}</a></td>
@if ($d->has_pre_terrain)
    <td>{!!$d->pre_terrain->symbol!!}</td>
    <td>{{$d->pre_terrain->name}}</td>
@elseif ($d->pre_flags)
    <td></td>
    <td>is:{{is_array($d->pre_flags) ? implode(", ", $d->pre_flags) : $d->pre_flags}}</td>
@else
    <td></td>
    <td></td>
@endif
@if ($d->has_post_terrain) 
    <td>{!!$d->post_terrain->symbol!!}</td>
    <td>{{$d->post_terrain->name}}</td>
@else
    <td></td>
    <td></td>
@endif
</tr>
@endforeach
</table>

