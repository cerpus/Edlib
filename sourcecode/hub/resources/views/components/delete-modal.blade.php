<!-- Delete Modal -->
<div class="modal fade" id="deletionModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deletionModelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content mt-4">
            <div class="modal-body text-center mt-4">
                <h2 class="mb-2" id="deletionModelLabel">Are you sure you want to delete the content?</h2>

                <div class="mt-4 mb-1">
                    <h6 class="mb-4">The content will be permanently deleted on Edlib and in other contexts.</h6>
                </div>

                <div class="mt-4 mb-4">
                    <button type="button" class="btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#deletionConfirmationModal">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deletion Confirmation Modal -->
<div class="modal fade" id="deletionConfirmationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deletionConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content mt-4">
            <div class="modal-body text-center mt-4">
                <h2 class="mb-2" id="staticBackdropLabel">{{ trans('messages.alert-delete-content-success') }}</h2>

                <div class="display-5 mb-3">
                    <x-icon name="check-circle" class="bi bi-check-circle"/>
                </div>

                <div class="mt-4 mb-4">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Go back</button>
                </div>
            </div>
        </div>
    </div>
</div>
