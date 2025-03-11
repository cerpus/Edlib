<div class="panel-body row">
    @include('fragments.invalidFlashMessage')
    <form method="post" enctype="multipart/form-data" id="h5p-library-form" class="col-md-6">
        <div class="form-group">
            <input type="file" name="h5p_file" id="h5p-file"/>
        </div>
        <div class="form-group h5p-disable-file-check">
            <div class="checkbox">
                <label><input type="checkbox" name="h5p_upgrade_only" id="h5p-upgrade-only"/> Only update existing libraries</label>
            </div>
            <div class="checkbox">
                <label><input type="checkbox" name="h5p_disable_file_check" id="h5p-disable-file-check"/> Disable file extension check</label>
            </div>
        </div>
        @isset($activetab)
            <input type="hidden" name="activetab" value="{{$activetab}}">
        @endisset
        @csrf
        <div id="form-group major-publishing-actions" class="submitbox">
            <input type="submit" name="submit" value="Upload" class="button button-primary button-large btn btn-primary"/>
        </div>
    </form>
</div>
