<?php

namespace App\View\Components;

use App\Models\Content;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ContentCard extends Component
{
    public function __construct(
        public readonly Content $content,
    ) {
    }

    public function render(): View
    {
        return view('components.content-card');
    }
}
