<nav class="navbar navbar-default" role="navigation">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#item-menu">
        <span class="sr-only">Toggle menu</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="item-menu">
      <ul class="nav navbar-nav">
        <li class="active"><a href="{{ route($areas['view']['route'], array('id'=>$itembunch[0]->id), array('class'=>'navbar-brand')) }}">Menu</a>
        @foreach($areas as $area=>$data)
        <li{!!$area==$active?' class="active"':''!!}><a href="{{ route($data['route'], array('id'=>$itembunch[0]->id)) }}">{{$data['label']}}</a>
        @endforeach
      </ul>
    </div>
  </div>
</nav>
