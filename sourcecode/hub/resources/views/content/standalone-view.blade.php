<x-layout>
    <x-slot name="title">{{ trans('messages.standalone-view') }}</x-slot>

    <div class="container">
        <div class="row justify-content-center">
            <div class="row justify-content-center">
                <x-lti-launch :launch="$launch" />
            </div>
        </div>

        <div class="row justify-content-center mt-4">
            <div class="">
                <div class="buttons-container d-flex justify-content-center">
                    <button type="button" class="btn btn-secondary mx-2" data-bs-toggle="modal" data-bs-target="#reportModal">
                        {{ trans('messages.report-content')}}
                    </button>

                    <button type="button" class="btn btn-primary mx-2">
                        {{ trans('messages.more-details')}}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Content Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="reportModalLabel">{{ trans('messages.report-this-content')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body border-0">
                    <p>{{ trans('messages.this-game-has-content')}}</p>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button class="btn btn-secondary btn-sm p-5 m-1" type="button" aria-label="Copyrighted">{{ trans('messages.copyrighted')}}</button>
                        <button class="btn btn-secondary btn-sm p-5 m-1" type="button" aria-label="Violent">{{ trans('messages.violent')}}</button>
                        <button class="btn btn-secondary btn-sm p-5 m-1" type="button" aria-label="Sexual">{{ trans('messages.sexual')}}</button>
                        <button class="btn btn-secondary btn-sm p-5 m-1" type="button" aria-label="Other">{{ trans('messages.other')}}</button>
                    </div>
                    <div class="mt-4">
                        <textarea class="form-control" placeholder="Describe the problem here..." aria-label="Problem Description"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-center align-items-center py-4">
                    <button type="button" class="btn btn-primary mx-2" data-bs-toggle="modal" data-bs-target="#reportSuccessModal" aria-label="Report Content"> {{ trans('messages.report-content')}}</button>
                    <button type="button" class="btn btn-secondary mx-2" data-bs-dismiss="modal" aria-label="Cancel"> {{ trans('messages.cancel')}}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Content Success Modal -->
    <div class="modal fade" id="reportSuccessModal" tabindex="-1" aria-labelledby="reportSuccessModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 text-center">
                    <i class="bi bi-check-circle fs-1 mx-auto" aria-label="Check Mark Icon"></i>
                </div>
                <div class="modal-body text-center">
                    <h3 class="font-weight-bold mb-3" aria-label="Reported Successfully">{{ trans('messages.reported-successfully')}}</h3>
                    <p>{{ trans('messages.we-will-look-into-this-shortly')}}</p>
                    <p>{{ trans('messages.thanks-for-reporting')}}</p>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-center align-items-center py-4">
                    <button type="button" class="btn btn-primary mx-2" data-bs-dismiss="modal" aria-label="Go Back">{{ trans('messages.go-back')}}</button>
                    <button type="button" class="btn btn-secondary mx-2" aria-label="Go to Edlib">{{ trans('messages.go-to-edlib')}}</button>
                </div>
            </div>
        </div>
    </div>
</x-layout>
