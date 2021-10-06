<form method="post" enctype="multipart/form-data" id="h5p-library-form">
        <div id="minor-publishing" class="panel panel-default">
            <div class="panel-heading">
                <h3>Upload or Update h5p libraries</h3>
                <p>File must be .h5p format.</p>
            </div>
            <div class="panel-body">
                <input type="file" name="h5p_file" id="h5p-file"/>
                <br>
                <div class="h5p-disable-file-check">
                    <label><input type="checkbox" name="h5p_upgrade_only" id="h5p-upgrade-only"/> Only update existing libraries</label>
                    <br>
                    <label><input type="checkbox" name="h5p_disable_file_check" id="h5p-disable-file-check"/> Disable file extension check</label>
                </div>
                <br>
                <input type="hidden" id="lets_upgrade_that" name="lets_upgrade_that" value="228e7591a1">
                <input type="hidden" name="_wp_http_referer" value="/wordpress/wp-admin/admin.php?page=h5p_libraries">
                {!! csrf_field() !!}
                <div id="major-publishing-actions" class="submitbox">
                    <input type="submit" name="submit" value="Upload" class="button button-primary button-large btn btn-primary"/>
                </div>
            </div>
        </div>
</form>

@include('fragments.invalidFlashMessage')

@if ($settings != NULL)
    <table class="table">
        <tr>
            <th>Title</th>
            <th>#contents</th>
            <th>#libdependencies</th>
            <th>upgrade</th>
            <th>details</th>
            <th>delete</th>
        </tr>
        @forelse ( $settings['libraryList']['listData'] as $library)
            <tr>
                <td>{{ $library['title'] }}</td>
                <td>{{ $library['numContent'] }}</td>
                <td>{{ $library['numLibraryDependencies'] }}</td>
                <td>{!! !empty($library['upgradeUrl']) ? HTML::link($library['upgradeUrl'], '', ['class'=>"fa fa-rocket"]) : '' !!}</td>
                <td><a href="{{ $library['detailsUrl'] }}"><i class="fa fa-search"></i></a></td>
                <td>
                    @if ($library['numLibraryDependencies'] < 1 && $library['numContent'] < 1)
                        <a href="{{ $library['deleteUrl'] }}" class="secure_confirm"><i class="fa fa-times"> </i></a>
                    @endif
                </td>
            </tr>
        @empty
            <td colspan="6"><p>No old content</p></td>
        @endforelse
    </table>
@endif
