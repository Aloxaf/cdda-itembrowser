@section('title')
专长 - CDDA 物品浏览器
@endsection
<h1>专长</h1>

<div class="row">
  <div class="col-md-3">
    <ul class="nav nav-pills nav-stacked">
      @foreach($proficiencies as $proficiency)
      <li class="@if($proficiency->id==$id) active @endif">
        <a href="{{ route(Route::currentRouteName(), $proficiency->id) }}">{{{$proficiency->name}}}</a>
      </li>
      @endforeach
    </ul>
  </div>
  <div class="col-md-9">
    @if (!$id)
    请在左侧选择一项
    @else
    <table class="table table-bordered table-hover tablesorter">
      <thead>
        <tr>
          <th></th>
          <th>名称</th>
        </tr>
      </thead>
      @foreach($items as $item)
      <tr>
        <td>{!! $item->symbol !!}</td>
        <td><a href="{{route('item.view', $item->id)}}">{{ $item->name }} {!! $item->modLabel !!}</a></td>
      </tr>
      </tr>
      @endforeach
    </table>
    @endif
  </div>
</div>