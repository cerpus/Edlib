<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Content;
use App\Models\ContentVersion;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SharedContentSearch extends Component
{
    public string $query = '';
    public string $filterLang = '';
    public string $sortBy = '';

    /**
     * @var array<mixed>
     */
    protected $queryString = [
        'query' => ['as' => 'q'],
        'filterLang' => ['as' => 'fl'],
        'sortBy' => ['as' => 'sort'],
    ];

    public function render(): View
    {
        $filter = [];
        if ($this->filterLang !== '') {
            $filter['lang'] = $this->filterLang;
        }

        $results = Content::findShared($this->query, $filter, $this->sortBy);

        return view('livewire.content.search', [
            'results' => $results->paginate(),
            'languageOptions' => ContentVersion::getTranslatedUsedLocales(),
            'sortOptions' => [
                'updated' => trans('messages.sort-last-updated'),
                'created' => trans('messages.sort-last-created'),
            ],
        ]);
    }
}
