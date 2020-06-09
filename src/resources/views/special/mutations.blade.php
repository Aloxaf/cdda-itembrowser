@section('title')
变异分类 - Cataclysm: Dark Days Ahead
@endsection
<div class="row">
  <div class="col-md-3">
    <ul class="nav nav-pills nav-stacked tsort">
      @foreach($categories as $key=>$category)
        @php
          $cid = is_string($category) ? $category : $category->id;
          $cname = is_string($category) ? $category : $category->name;
        @endphp
        <li class="@if ($cid == $id) active @endif@"><a href="{{ route(Route::currentRouteName(), $cid) }}">{{ $cname }}</a></li>
      @endforeach
    </ul>
  </div>
  <div class="col-md-9">
    @if(!$id)
      Please select an entry from the menu on the left.
    @else
      <ul class="list-unstyled">
        <table class="table table-bordered tablesorter">
          <thead>
            <tr>
              <th>名称</th>
              <th>点数消耗</th>
              <th>可见性</th>
              <th>丑陋</th>
            </tr>
          </thead>
          @foreach($muts as $mut)
            <tr>
              <td><a href="{{ route('special.mutation', array($mut->id)) }}">{{ $mut->name }} {!! $mut->modLabel !!}</a></td>
              <td class="text-right">{{ $mut->points }}</td>
              <td class="text-right">{{ $mut->visibility ?: 0 }}</td>
              <td class="text-right">{{ $mut->ugliness ?: 0 }}</td>
            </tr>
          @endforeach
        </table>
        <script>
          $(function() {
            $(".tablesorter").tablesorter({
              sortList: [
                [1, 0]
              ]
            });
          });
        </script>
      </ul>
    @endif
  </div>
</div>