<div class="panel panel-default panel-default" id="attribution">
    <div class="panel-heading">{{ trans('article.attribution') }}</div>

    <div class="panel-body">
        {!! Form::label('origin', trans('attribution.primary-origin')) !!}
        {!! Form::text('origin', $origin ?? null, ['class' => 'attr-origin form-control']) !!}
    </div>

    <hr>

    <div class="panel-body">
        <div class="form-group">
            {!! Form::label('attr-role', trans('attribution.role')) !!}
            {!! Form::select('attr-role', [
                'Source' => trans('attribution.source'),
                'Supplier' => trans('attribution.supplier'),
                'Writer' => trans('attribution.writer'),
            ], null, ['placeholder' => trans('common.select-one'), 'class' => 'form-control', 'name' => '']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('attr-name', trans('attribution.name')) !!}
            {!! Form::text('attr-name', null, ['class' => 'form-control', 'name' => '']) !!}
        </div>

        <div class="btn-group">
            {!! Form::button(trans('attribution.add-originator'), ['class' => 'btn btn-success', 'id' => 'attr-add']) !!}
        </div>
    </div>

    <ul class="list-group" id="attr-list">
        @foreach ($originators ?? [] as $originator)
            @component('fragments.originator-list-item')
                @slot('name', $originator->name)
                @slot('role', $originator->role)
                @slot('index', $loop->index)
            @endcomponent
        @endforeach
    </ul>
</div>

@push('js')
    <script>
        (function () {
            var TEMPLATE = {!! json_encode(view('fragments.originator-list-item', [
                'name' => '__NAME__',
                'role' => '__ROLE__',
                'index' => '__INDEX__'
            ])->render()) !!};

            var $name = $('#attr-name')
                .on('input', onFieldChange)
                .on('keydown', function (event) {
                    if (event.which === 13) {
                        event.preventDefault();

                        $submit.trigger('click');
                    }
                });

            var $role = $('#attr-role')
                .change(onFieldChange);

            var $submit = $('#attr-add')
                .click(function () {
                    if (!$submit.prop('disabled')) {
                        var listItem = createListItem($name.val(), $role.val());
                        $('#attr-list').append(createListItem($name.val(), $role.val()));

                        resetFields();
                    }
                });

            var $list = $('#attr-list');

            onFieldChange();

            $(document).on('click', '.attr-remove', function () {
                $(this).parents('li').remove();
            });

            function resetFields() {
                $name.val('').trigger('change');
                $role.val('').trigger('change');
            }

            var i = {{ count($originators ?? []) }};

            function createListItem(name, role) {
                var index = String(i++);

                return $(
                    TEMPLATE
                        .replace(/__NAME__/g, name.replace('<', '&lt;').replace('>', '&gt;').replace('&', '&amp;'))
                        .replace(/__ROLE__/g, role.replace('<', '&lt;').replace('>', '&gt;').replace('&', '&amp;'))
                        .replace(/__INDEX__/g, index)
                );
            }

            function onFieldChange() {
                $submit.prop('disabled', !$role.val() || $name.val().trim() === '');
            }
        })();
    </script>
@endpush

@push('css')
    <style>
        #attr-list {
            padding: 0;
        }

        #attr-list li {
            word-break: break-word;
        }

        #attr-list:empty {
            display: none;
        }
    </style>
@endpush
