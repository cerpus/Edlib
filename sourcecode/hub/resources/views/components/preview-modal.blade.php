<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-lg-down modal-lg">
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
                <a
                    id="previewShareLink"
                    href=""
                    class="btn btn-secondary d-flex gap-2 share-button"
                    role="button"
                    data-share-success-message="{{ trans('messages.share-copied-url-success') }}"
                    data-share-failure-message="{{ trans('messages.share-copied-url-failed') }}"
                    target="_blank"
                    hidden
                >
                    <x-icon name="share" />
                    {{ trans('messages.share') }}
                </a>
                <a id="previewEditButton" href="" class="btn btn-secondary" role="button" hidden>
                    {{ trans('messages.edit-content') }}
                </a>
                <x-form action="" method="POST" id="previewUseForm" hidden>
                    <button class="btn btn-primary" role="button">
                        {{ trans('messages.use-content') }}
                    </button>
                </x-form>
            </div>
        </div>
    </div>
</div>
<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
    const previewModal = document.querySelector('#previewModal');
    previewModal.addEventListener('show.bs.modal', event => {
        const initiator = event.relatedTarget;
        const created = initiator.getAttribute('data-content-created');
        const updated = initiator.getAttribute('data-content-updated');
        const title = initiator.getAttribute('data-content-title');
        const previewUrl = initiator.getAttribute('data-content-preview-url');
        const shareUrl = initiator.getAttribute('data-content-share-url');
        const editUrl = initiator.getAttribute('data-content-edit-url');
        const useUrl = initiator.getAttribute('data-content-use-url');

        fetch(previewUrl, {
            method: 'POST',
            headers: {
                'x-csrf-token': '{{ csrf_token() }}'
            }
        })
            .then(response => {
                if (response.ok) {
                    return response.text();
                }
                throw new Error(response.statusText || response.status);
            })
            .then(content => {
                previewModal.querySelector('#previewContent').innerHTML = content;
            })
            .catch(error => {
                const errElm = document.createElement('div');
                errElm.classList.add('alert', 'alert-danger');
                errElm.textContent = '{{trans('messages.error-loading-preview')}}' + ` (${error.message})`;
                previewModal.querySelector('#previewContent').appendChild(errElm);
            })
        ;

        previewModal.querySelector('#previewModalTitle').textContent = title;
        previewModal.querySelector('#previewUpdatedAt').textContent = updated;
        previewModal.querySelector('#previewCreatedAt').textContent = created;

        if (shareUrl) {
            previewModal.querySelector('#previewShareLink').hidden = false;
            previewModal.querySelector('#previewShareLink').href = shareUrl;
        } else {
            previewModal.querySelector('#previewShareLink').hidden = true;
        }

        if (editUrl) {
            previewModal.querySelector('#previewEditButton').hidden = false;
            previewModal.querySelector('#previewEditButton').href = editUrl;
        } else {
            previewModal.querySelector('#previewEditButton').hidden = true;
        }

        if (useUrl) {
            previewModal.querySelector('#previewUseForm').hidden = false;
            previewModal.querySelector('#previewUseForm').action = useUrl;
        } else {
            previewModal.querySelector('#previewUseForm').hidden = true;
        }
    });

    previewModal.addEventListener('hidden.bs.modal', () => {
        previewModal.querySelector('#previewContent').innerHTML = '';
        previewModal.querySelector('#previewModalTitle').textContent = '';
        previewModal.querySelector('#previewShareLink').href = '';
        previewModal.querySelector('#previewEditButton').href = '';
        previewModal.querySelector('#previewUpdatedAt').textContent = '';
        previewModal.querySelector('#previewCreatedAt').textContent = '';
        previewModal.querySelector('#previewUseForm').action = '';
    });
</script>
