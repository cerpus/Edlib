@extends('layouts.admin')

@section('content')
    <div style="width: 75vw;margin-right: auto;margin-left: auto;">
        <div class="row">
            <div class="page-header">
                <h3>
                    Manage content bulk exclution
                </h3>
            </div>
            <div class="panel with-nav-tabs panel-default">
                <div class="panel-heading">
                    <ul class="nav nav-tabs">
                        <li @class(['active' => $activeTab === 'tabExcluded'])>
                            <a href="#tabExcluded" data-toggle="tab">
                                Excluded content
                            </a>
                        </li>
                        <li @class(['active' => $activeTab === 'tabFind'])>
                            <a href="#tabFind" data-toggle="tab">
                                Find content
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="panel-body">
                    <div class="tab-content">
                        <div
                            id="tabExcluded"
                            @class([
                                "tab-pane fade",
                                'in active' => $activeTab === 'tabExcluded',
                            ])
                        >
                            @include('admin.bulkexclude.list-excluded')
                        </div>
                        <div
                            @class([
                                "tab-pane fade",
                                'in active' => $activeTab === 'tabFind',
                            ])
                            id="tabFind"
                        >
                            @include('admin.bulkexclude.find-content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
