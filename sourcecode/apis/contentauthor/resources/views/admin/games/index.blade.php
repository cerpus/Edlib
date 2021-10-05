@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Games</div>
                    <div class="panel-body">
                        @if (session('message'))
                            <div class="alert alert-info">
                                {{ session('message') }}
                            </div>
                        @endif
                        <div id="minor-publishing" class="panel panel-default">
                            <form action="{{ route('admin.games.store') }}"
                                  method="post"
                                  enctype="multipart/form-data"
                                  id="games-form">
                                {!! csrf_field() !!}
                                <div class="panel-heading">
                                    <h3>Update games</h3>
                                    <p>File must be .zip format.</p>
                                </div>
                                <div class="panel-body">
                                    @if ( $errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <input type="file" name="gameFile" id="file"/>
                                    <br>
                                    <div id="major-publishing-actions" class="submitbox">
                                        <button type="submit" name="submit"
                                                class="button button-primary button-large btn btn-primary">Upload game
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="panel panel-default">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Version</th>
                                    <th>Games</th>
                                    <th>Installed</th>
                                    <th>Updated</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>Name</th>
                                    <th>Version</th>
                                    <th>Games</th>
                                    <th>Installed</th>
                                    <th>Updated</th>
                                </tr>
                                </tfoot>
                                <tbody>
                                @forelse ($gameTypes as $gameType)
                                    <tr>
                                        <td>{{ $gameType->title }}</td>
                                        <td>{{ $gameType->getVersion() }}</td>
                                        <td>{{ $gameType->games()->count() }}</td>
                                        <td>{{ $gameType->created_at->format('d.m.y H:i:s') }}</td>
                                        <td>{{ $gameType->updated_at->format('d.m.y H:i:s') }}</td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">No games uploaded</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
