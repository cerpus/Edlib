<!-- Delete Modal -->
<div class="modal fade" id="deletionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deletionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header text-center border-0">
                <h2 class="w-100" id="deletionModalLabel">{{ trans('messages.alert-delete-content-header') }}</h2>
            </div>

            <div class="modal-body text-center">
                <p class="mb-1">{{ trans('messages.alert-delete-content-description') }}</p>
            </div>

            <div class="modal-footer text-center border-0">
                <button type="button" class="btn btn-primary me-3" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#deletionConfirmationModal">{{ trans('messages.delete') }}</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('messages.cancel') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Deletion Confirmation Modal -->
<div class="modal fade" id="deletionConfirmationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deletionConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header text-center border-0">
                <h2 class="w-100">{{ trans('messages.alert-delete-content-success') }}</h2>
            </div>

            <div class="modal-body text-center display-5">
                <x-icon name="check-circle" class="bi bi-check-circle"/>
            </div>

            <div class="modal-footer text-center border-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{{ trans('messages.go-back') }}</button>
            </div>
        </div>
    </div>
</div>
