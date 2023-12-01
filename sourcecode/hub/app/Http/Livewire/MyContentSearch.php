<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Content;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MyContentSearch extends Component
{
    public User $user;

    public string $query = '';

    /**
     * @var array<mixed>
     */
    protected $queryString = [
        'query' => ['as' => 'q'],
    ];

    public function render(): View
    {
        $results = Content::findForUser($this->user, $this->query);

        return view('livewire.content.search', [
            'mine' => true,
            'results' => $results->paginate(),
        ]);
    }
}
