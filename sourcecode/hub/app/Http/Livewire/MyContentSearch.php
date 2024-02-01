<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MyContentSearch extends Component
{
    public User $user;

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

        $results = Content::findForUser($this->user, $this->query, $filter, $this->sortBy);

        return view('livewire.content.search', [
            'mine' => true,
            'results' => $results->paginate(),
            'languageOptions' => ContentVersion::getTranslatedUsedLocales($this->user),
            'sortOptions' => [
                'updated' => trans('messages.sort-last-updated'),
                'created' => trans('messages.sort-last-created'),
            ],
        ]);
    }
}
