<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.site-name') }} </title>

    <!-- Styles -->
    <link href="{{ mix('css/admin.css') }}" rel="stylesheet">
    <link href="{{ mix('css/font-awesome.css') }}" rel="stylesheet">
@stack('styles')

<!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
</head>
<body>
<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="collapse navbar-collapse" id="app-navbar-collapse">
            <a class="navbar-brand" href="{{ route('admin') }}">
                {{ config('app.site-name'). ' Admin' }}
            </a>
            <ul class="nav navbar-nav navbar-right">
                <!-- Authentication Links -->
                @if (Auth::guest())
                    <li><a href="{{ route('login') }}">Login</a></li>
                @else
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            Admin <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a href="{{ route('admin.presave.index') }}">Presave</a>
                            </li>
                            <li>
                                <a href="{{ route('admin.support.versioning') }}">Versioning</a>
                            </li>
                        </ul>
                    </li>
                    @if( config('h5p.isHubEnabled') !== true )
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                               aria-expanded="false">
                                Articles <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <a href="{{ route('admin.article.maxscore.list') }}">Max score
                                        {{--
                                        <span class="label label-info">{{\App\Article::noMaxScore()->ofBulkCalculated(\App\Article::BULK_UNTOUCHED)->count()}}</span>
                                        <span class="label label-danger">{{\App\Article::ofBulkCalculated(\App\Article::BULK_FAILED)->count()}}</span>
                                    --}}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            H5P <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a href="{{ route('admin.capability') }}">Capabilities</a>
                            </li>
                            <li>
                                <a href="{{ route('admin.update-libraries') }}">Manage content types</a>
                            </li>
                            @if( resolve(\App\Libraries\H5P\Interfaces\H5PAdapterInterface::class)->useMaxscore() )
                                <li>
                                    <a href="{{ route('admin.maxscore.list') }}">Max score
                                        {{--
                                        <span class="label label-info">{{\App\H5PContent::noMaxScore()->count()}}</span>
                                        <span class="label label-danger">{{\App\H5PContent::ofBulkCalculated(\App\Libraries\H5P\H5PLibraryAdmin::BULK_FAILED)->count()}}</span>
                                        --}}
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a href="{{ route('admin.migrate.library-content') }}">Migrate library content</a>
                            </li>
                        </ul>
                    </li>
                    @if(app(\App\Libraries\H5P\Interfaces\H5PAdapterInterface::class)->getAdapterName() !== 'ndla')
                        <li>
                            <a href="{{ route('admin.games') }}">Games</a>
                        </li>
                    @endif
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            {{ Auth::user()->name }} <span class="caret"></span>
                        </a>

                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a href="#"
                                   onclick="
                                        event.preventDefault();
                                        document.getElementById('logout-form').submit();">
                                    Logout
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                      style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>

@yield('content')

<!-- Scripts -->
<script src="{{ mix('js/admin.js') }}"></script>
@stack('js')
</body>
</html>
