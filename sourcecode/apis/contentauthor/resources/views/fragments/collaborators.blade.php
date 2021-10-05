@if(config('feature.collaboration'))
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">
                {{ trans('h5p-editor.add-email') }}
            </div>
            <div class="panel-body">
                {!! Form::text("col-email", "", array(
                "id" => "col-email",
                "placeholder" => trans("common.email-address"),
                "class" => "input")) !!}
                {!! Form::hidden("col-emails", $emails, array("id" => "col-emails")) !!}
                <button class="btn btn-success" type="button" id="add-collab-btn"
                        onclick="return false;">{{trans('common.add')}}</button>
                <ul id="collab-list"></ul>
            </div>
        </div>

    </div>

    @push('js')
        <script type="text/javascript">
            $(document).ready(function () {
                collabemails = [];

                var preemailsraw = $('#col-emails').val();
                var preemails = preemailsraw.split(',');

                for (var i = 0; i < preemails.length; i++) {
                    if (validateEmail(preemails[i]))
                        collabemails.push(preemails[i]);
                }

                updateList(collabemails);

                $('#add-collab-btn').on('click', function (e) {
                    addtoList();
                });

                function addtoList() {
                    var fieldval = $('#col-email');
                    if (fieldval.val().length > 4 && validateEmail(fieldval.val())) {
                        if (collabemails.indexOf(fieldval.val()) == "-1") {
                            collabemails.push(fieldval.val());
                            updateList(collabemails);
                        }
                    }
                    fieldval.val("");
                    fieldval.focus();
                }

                function updateList(collabemails) {
                    $('#collab-list').html("");
                    for (var i = 0; i < collabemails.length; i++)
                        $('#collab-list').append('<li>' + collabemails[i] + '</li>');

                    $('#col-emails').val(collabemails);
                }

                function validateEmail(email) {
                    var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    return re.test(email);
                }

                $('form #col-email').on('keypress', function (e) {
                    if (e.which == 13) {
                        addtoList();
                    }
                    return e.which !== 13;
                });

                $("#collab-list").on("click", "li", function (event) {
                    collabemails.splice($(this).index(), 1);
                    updateList(collabemails);
                });

                var tmpmail;
                $("#collab-list")
                    .on("mouseenter", "li", function (event) {
                        tmpmail = $(this).text();
                        $(this).text('click to remove');
                        $(this).css('color', 'red');
                        $(this).css('cursor', 'pointer');
                    })
                    .on("mouseleave", "li", function (event) {
                        $(this).text(tmpmail);
                        $(this).css('color', 'black');
                        $(this).css('cursor', 'default');
                    });
            });
        </script>
    @endpush
@endif
