<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Content;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SharedContentSearch extends Component
{
    public string $query = '';

    /**
     * @var array<mixed>
     */
    protected $queryString = [
        'query' => ['as' => 'q'],
    ];

    public function render(): View
    {
        $results = Content::findShared($this->query);

        return view('livewire.content.search', [
            'results' => $results->paginate(),
        ]);
    }
}
