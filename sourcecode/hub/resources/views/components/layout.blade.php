<x-layout.base :nav="$nav ?? true">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-white sidebar">
                <div class="position-sticky">
                    <!-- Sidebar content -->
                    <div class="accordion p-3 m-0 border-0 bd-example m-0 border-0" id="filterAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button id="language" class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Language
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
                                <div class="accordion-body p-0">
                                    <ul class="list-group">
                                        <li class="list-group-item border-0">
                                            <input class="form-check-input me-1" type="checkbox" value="english" id="english">
                                            <label class="form-check-label" for="firstCheckbox">English</label>
                                        </li>
                                        <li class="list-group-item border-0">
                                            <input class="form-check-input me-1" type="checkbox" value="bokmål" id="bokmal">
                                            <label class="form-check-label" for="secondCheckbox">Bokmål</label>
                                        </li>
                                        <li class="list-group-item border-0">
                                            <input class="form-check-input me-1" type="checkbox" value="nynorsk" id="nynorsk">
                                            <label class="form-check-label" for="thirdCheckbox">Nynorsk</label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button id="media" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Media
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                <div class="accordion-body p-0">
                                    <ul class="list-group">
                                        <li class="list-group-item border-0">
                                            <input class="form-check-input me-1" type="checkbox" value="" id="firstMedia">
                                            <label class="form-check-label" for="firstCheckbox">First</label>
                                        </li>
                                        <li class="list-group-item border-0">
                                            <input class="form-check-input me-1" type="checkbox" value="" id="secondMedia">
                                            <label class="form-check-label" for="secondCheckbox">Second</label>
                                        </li>
                                        <li class="list-group-item border-0">
                                            <input class="form-check-input me-1" type="checkbox" value="" id="thirdMedia">
                                            <label class="form-check-label" for="thirdCheckbox">Third</label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button id="visuals" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Visuals
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                <div class="accordion-body p-0">
                                    <ul class="list-group">
                                        <li class="list-group-item border-0">
                                            <input class="form-check-input me-1" type="checkbox" value="" id="firstVisuals">
                                            <label class="form-check-label" for="firstCheckbox">First</label>
                                        </li>
                                        <li class="list-group-item border-0">
                                            <input class="form-check-input me-1" type="checkbox" value="" id="secondVisuals">
                                            <label class="form-check-label" for="secondCheckbox">Second</label>
                                        </li>
                                        <li class="list-group-item border-0">
                                            <input class="form-check-input me-1" type="checkbox" value="" id="thirdVisuals">
                                            <label class="form-check-label" for="thirdCheckbox">Third</label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button id="task" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    Task
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                <div class="accordion-body p-0">
                                    <ul class="list-group">
                                        <li class="list-group-item border-0">
                                            <input class="form-check-input me-1" type="checkbox" value="" id="firstTask">
                                            <label class="form-check-label" for="firstCheckbox">First</label>
                                        </li>
                                        <li class="list-group-item border-0">
                                            <input class="form-check-input me-1" type="checkbox" value="" id="secondTask">
                                            <label class="form-check-label" for="secondCheckbox">Second</label>
                                        </li>
                                        <li class="list-group-item border-0">
                                            <input class="form-check-input me-1" type="checkbox" value="" id="thirdTask">
                                            <label class="form-check-label" for="thirdCheckbox">Third</label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button id="quizzes" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                    Quizzes
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                <div class="accordion-body p-0">
                                    <ul class="list-group">
                                        <li class="list-group-item border-0">
                                            <input class="form-check-input me-1" type="checkbox" value="" id="firstQuizzes">
                                            <label class="form-check-label" for="firstCheckbox">First</label>
                                        </li>
                                        <li class="list-group-item border-0">
                                            <input class="form-check-input me-1" type="checkbox" value="" id="secondQuizzes">
                                            <label class="form-check-label" for="secondCheckbox">Second</label>
                                        </li>
                                        <li class="list-group-item border-0">
                                            <input class="form-check-input me-1" type="checkbox" value="" id="thirdQuizzes">
                                            <label class="form-check-label" for="thirdCheckbox">Third</label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <header>
                    <h1 class="fs-2">{{ $title }}</h1>
                </header>
                <!-- main content -->
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layout.base>
