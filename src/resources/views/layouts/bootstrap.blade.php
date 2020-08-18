<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="@yield('description', 'roguelike 游戏 CDDA 的物品/配方浏览器。借助它你可以查询物品并提前计划！')">
  <meta name="author" content="Sergio Duran">

  <title>@yield('title', 'Cataclysm: Dark Days Ahead 物品浏览器')</title>

  <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />

  <!-- Bootstrap core CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.1.1/dist/css/bootstrap.min.css">

  <!-- Custom styles for this template -->
  <link href="/css/starter-template.css" rel="stylesheet">

  <link href="/css/cataclysm.css?v=3" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.0.1/build/styles/tomorrow-night-bright.min.css" rel="stylesheet">

  <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

  <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-162534158-1"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'UA-162534158-1');
  </script>
  <!-- Google ad -->
  <script data-ad-client="ca-pub-1206456801696320" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
</head>

<body class="terminal">

  <div class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="{{ url('/') }}">CDDA 物品浏览器</a>
      </div>
      <div class="collapse navbar-collapse">
        <ul class="nav navbar-nav">
          @foreach($sites as $label=>$domain)
            <li {{ ($_SERVER["SERVER_NAME"]==$domain? ' class="active"':'') }}><a href="http://{{ $domain.$_SERVER["REQUEST_URI"] }}">{{ $label }}</a>
          @endforeach
        </ul>
        <div class="col-sm-3 pull-right">
          <form class="navbar-form" role="form" action="<?= action("HomeController@search") ?>">

            <div class="input-group">
              <input name="q" type="text" placeholder="搜索..." class="form-control" value="{{ $q }}">
              <span class="input-group-btn">
                <button type="submit" class="btn btn-success">Go</button>
              </span>
            </div>
          </form>
        </div>

      </div>
      <!--/.nav-collapse -->
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/jquery@1.11.0/dist/jquery.min.js"></script>
  <script src="/js/jquery.tablesorter.min.js"></script>
  <script src="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.0.1/build/highlight.min.js"></script>
  <script src="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@10.0.1/build/languages/json.min.js"></script>
  <div class="container">
    {!! $content !!}
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.1.1/dist/js/bootstrap.min.js"></script>
</body>

@if(View::exists('layouts.extra_footer'))
  @include('layouts.extra_footer')
@endif

</html>