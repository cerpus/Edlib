<x-layout>
    <x-slot name="title">{{ trans('messages.standalone-view') }}</x-slot>
    <div class="container">
        <div class="row justify-content-center">
            <div class="row justify-content-center">
                <x-lti-launch :launch="$launch" />
            </div>
        </div>
        <div class="row justify-content-center mt-4">
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
    <!-- Report Content Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="reportModalLabel">{{ trans('messages.report-this-content')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ trans('messages.close')}}"></button>
                </div>
                <div class="modal-body border-0">
                    <p>{{ trans('messages.select-the-reason-for-reporting-this-content')}}</p>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button class="btn btn-secondary btn-sm p-5 m-1" type="button">{{ trans('messages.copyright')}}</button>
                        <button class="btn btn-secondary btn-sm p-5 m-1" type="button">{{ trans('messages.violence')}}</button>
                        <button class="btn btn-secondary btn-sm p-5 m-1" type="button">{{ trans('messages.sexual')}}</button>
                        <button class="btn btn-secondary btn-sm p-5 m-1" type="button">{{ trans('messages.other')}}</button>
                    </div>
                    <div class="mt-4">
                        <textarea class="form-control" placeholder="{{ trans('messages.describe-the-problem-here')}}" aria-label="{{ trans('messages.problem-description')}}"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-center align-items-center py-4">
                    <button type="button" class="btn btn-primary mx-2" data-bs-toggle="modal" data-bs-target="#reportSuccessModal"> {{ trans('messages.report-content')}}</button>
                    <button type="button" class="btn btn-secondary mx-2" data-bs-dismiss="modal"> {{ trans('messages.cancel')}}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Report Content Success Modal -->
    <div class="modal fade" id="reportSuccessModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" aria-labelledby="reportSuccessModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 text-center">
                    <x-icon name="check-circle" id="reportSuccessModalLabel" class="bi fs-1 mx-auto" aria-hidden="true"/>
                </div>
                <div class="modal-body text-center">
                    <h3 class="font-weight-bold mb-3">{{ trans('messages.reported-successfully')}}</h3>
                    <p>{{ trans('messages.we-will-look-into-this-shortly')}}</p>
                    <p>{{ trans('messages.thanks-for-reporting')}}</p>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-center align-items-center py-4">
                    <button type="button" class="btn btn-primary mx-2" data-bs-dismiss="modal">{{ trans('messages.go-back')}}</button>
                    <button type="button" class="btn btn-secondary mx-2">{{ trans('messages.go-to-edlib')}}</button>
                </div>
            </div>
        </div>
    </div>
</x-layout>
