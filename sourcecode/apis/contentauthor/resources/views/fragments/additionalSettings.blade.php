{!! Form::hidden('embed', $display_options[H5PCore::DISPLAY_OPTION_EMBED]) !!}
<div>
    <div class="panel panel-default">
        <div class="panel-heading">
            {{ trans('h5p-editor.display-options') }}
        </div>
        <div class="panel-body optionBox">
            <label>
                {!! Form::checkbox('frame', 1, $display_options[H5PCore::DISPLAY_OPTION_FRAME]) !!} {{ trans('h5p-editor.display-action-bar') }}
            </label>
            <div class="optionsContainer">
                <label>
                    {!! Form::checkbox('copyright', 1, $display_options[H5PCore::DISPLAY_OPTION_COPYRIGHT]) !!} {{ trans('h5p-editor.copyright-button') }}
                </label>
            </div>
            <div class="optionsContainer">
                <label>
                    {!! Form::checkbox('download', 1, $display_options[H5PCore::DISPLAY_OPTION_DOWNLOAD]) !!} {{ trans('h5p-editor.download-button') }}
                </label>
            </div>
        </div>
    </div>
</div>
