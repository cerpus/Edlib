<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="article-title" content="{{ strip_tags($article->title) }}">
    <meta name="article-url" content="{{ route('article.show', $article) }}">
    <title>@yield('title', 'Article')</title>
    <link rel="stylesheet" href="{{ elixir('content_explorer_bootstrap.css') }}">
    <link rel="stylesheet" href="{{ elixir('h5picons.css') }}">
    <link rel="stylesheet" href="{{ elixir('react-article.css') }}">
    <link rel="stylesheet" href="{{ elixir('article.css') }}">
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Lato:400,700">
    @yield('customCSS')
    <script type="text/x-mathjax-config">
        MathJax.Hub.Config({
          tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}
        });
    </script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.5/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
</head>
<body>
@include('fragments.draft-editor')
<article class="{{$ndlaArticle === true ? 'ndla-article' : 'edlib-article'}}">
    @yield('content')
</article>
<script src="{{ elixir('bootstrap.js') }}"></script>
<script src="{{ elixir('article.js') }}"></script>
@stack('js')
</body>
</html>
