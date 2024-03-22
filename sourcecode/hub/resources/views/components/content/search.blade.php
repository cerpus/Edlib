@props(['contents', 'filter', 'mine' => false, 'showDrafts' => false])

<div class="mt-3 d-flex flex-column flex-max">
    <search class="col-12">
        <x-filter.top :$contents :$filter />
    </search>
    <section id="content" class="col-12 d-flex flex-column flex-max">
        <x-content.result :$contents :$filter :$mine :$showDrafts />
    </section>
</div>
