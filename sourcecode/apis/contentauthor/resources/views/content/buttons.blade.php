<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if (isset($jwtToken) && $jwtToken)
        <meta name="jwt" content="{{ $jwtToken }}"/>
    @endif
    <link href="{{ mix('css/content_explorer_bootstrap.css') }}" rel="stylesheet">
    <link href="{{ mix('css/font-awesome.css') }}" rel="stylesheet">
    <link href='//fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
</head>
<body>
    <div class="container-fluid">
        <div class="row center-block">
            <div class="contentTypeOuter clearfix">
                <ul class="content-types-button-view">
                    <li class="adapter-element">
                        @include('fragments.adapter-selector')
                    </li>
                    @foreach($contentTypes as $contenttype)
                        <li>
                            <a href="{{ $contenttype->createUrl }}">
                                <i class="material-icons">{{ $contenttype->icon }}</i>
                                <span>{{ $contenttype->title }}</span>
                                <span class="last">
                                    <i class="material-icons">chevron_right</i>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <script src="{{ mix('js/bootstrap.js') }}"></script>
    <script src="{{ asset('js/jwtclient.js') }}"></script>
    <script src="{{ asset('js/resource-common.js') }}"></script>
</body>
</html>
