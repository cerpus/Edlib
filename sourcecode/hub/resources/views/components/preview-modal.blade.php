<!-- Preview Modal -->
<div class="modal fade" id="previewModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="previewModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <div class="h4 modal-title" id="previewModalTitle"></div>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="{{trans('messages.close')}}"
                ></button>
            </div>

            <div class="modal-body">
                <a id="previewShareUrl" href="" target="_blank" class="text-body-emphasis" aria-description="{{trans('messages.link-to-share')}}"></a>
                <x-icon class="ms-3" name="share"/>
                <div id="previewContent" class="mt-5"></div>
            </div>

            <div class="modal-footer border-0">
                <div class="flex-fill">
                    <div>
                        <strong>{{trans('messages.created')}}:</strong>
                        <span id="previewCreatedAt"></span>
                    </div>
                    <div>
                        <strong>{{trans('messages.edited')}}:</strong>
                        <span id="previewUpdatedAt"></span>
                    </div>
                </div>
                <a id="previewUseButton" href="" class="btn btn-primary visually-hidden" role="button">
                    {{ trans('messages.use-content') }}
                </a>
                <a id="previewEditButton" href="" class="btn btn-secondary visually-hidden" role="button">
                    {{ trans('messages.edit-content') }}
                </a>
            </div>
        </div>
    </div>
</div>
<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
    const previewModal = document.getElementById('previewModal');
    if (previewModal) {
        previewModal.addEventListener('show.bs.modal', event => {
            const initiator = event.relatedTarget;
            const content = initiator.getAttribute('data-bs-content');
            const version = initiator.getAttribute('data-bs-version');
            const previewUrl = "{{route('content.preview', ['%content%', '%version%'])}}".replace('%content%', content).replace('%version%', version);
            const shareUrl = "{{route('content.share', '%content%')}}".replace('%content%', content);
            const editable = initiator.getAttribute('data-bs-editable') === '1';
            const editUrl = "{{route('content.edit', '%content%')}}";

            fetch(previewUrl, {
                method: 'POST',
                headers: {
                    'x-csrf-token': '{{ csrf_token() }}'
                }
            })
                .then(response => {
                    if (response.ok) {
                        return response.body.pipeThrough(new TextDecoderStream()).getReader().read();
                    }
                    throw new Error(response.statusText || response.status);
                })
                .then(content => {
                    previewModal.querySelector('#previewContent').innerHTML = content.value;
                })
                .catch(error => {
                    const errElm = document.createElement('div');
                    errElm.classList.add('alert', 'alert-danger');
                    errElm.textContent = '{{trans('messages.error-loading-preview')}}' + ` (${error.message})`;
                    previewModal.querySelector('#previewContent').appendChild(errElm);
                })
            ;

            previewModal.querySelector('#previewModalTitle').textContent = initiator.getAttribute('data-bs-title');
            previewModal.querySelector('#previewShareUrl').href = shareUrl;
            previewModal.querySelector('#previewShareUrl').textContent = shareUrl;
            previewModal.querySelector('#previewUpdatedAt').textContent = initiator.getAttribute('data-bs-updated');
            previewModal.querySelector('#previewCreatedAt').textContent = initiator.getAttribute('data-bs-created');
            if (editable) {
                previewModal.querySelector('#previewEditButton').classList.remove('visually-hidden');
                previewModal.querySelector('#previewEditButton').href = editUrl.replace('%content%', content);
            } else {
                previewModal.querySelector('#previewEditButton').classList.add('visually-hidden');
            }
        });

        previewModal.addEventListener('hidden.bs.modal', () => {
            previewModal.querySelector('#previewContent').innerHTML = '';
            previewModal.querySelector('#previewModalTitle').textContent = '';
            previewModal.querySelector('#previewShareUrl').href = '';
            previewModal.querySelector('#previewShareUrl').textContent = '';
            previewModal.querySelector('#previewEditButton').href = '';
            previewModal.querySelector('#previewUpdatedAt').textContent = '';
            previewModal.querySelector('#previewCreatedAt').textContent = '';
        });
    }
</script>
