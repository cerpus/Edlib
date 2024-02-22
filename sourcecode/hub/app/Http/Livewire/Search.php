<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Laravel\Scout\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;

use function session;
use function trans;

class Search extends Component
{
    public User|null $user = null;

    #[Url(as: 'q')]
    public string $query = '';

    #[Url(as: 'lang')]
    public string $language = '';

    #[Url(as: 'sort')]
    public string $sortBy = 'updated';

    public function render(): View
    {
        if ($this->user) {
            $contents = Content::findForUser($this->user, $this->query);
        } else {
            $contents = Content::findShared($this->query);
        }

        $contents
            ->when($this->language, fn (Builder $query) => $query->where('language_iso_639_3', $this->language))
            ->orderBy(match ($this->sortBy) {
                'created' => 'created_at',
                default => 'updated_at',
            }, 'desc')
        ;

        return view('livewire.content.search', [
            'contents' => $contents->paginate(),
            'layout' => session()->get('contentLayout', 'grid'),
            'languageOptions' => ContentVersion::getTranslatedUsedLocales($this->user),
            'sortOptions' => [
                'updated' => trans('messages.sort-last-updated'),
                'created' => trans('messages.sort-last-created'),
            ],
            'titlePreviews' => session()->has('lti'),
        ]);
    }
}
