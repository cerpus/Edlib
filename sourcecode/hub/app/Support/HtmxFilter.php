<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Laravel\Scout\Builder;

use function session;
use function trans;
use function view;

class HtmxFilter
{
    private User|null $user = null;
    private string $query;
    private string $language;
    private string $sortBy;

    public function __construct(Request $request, User|null $user = null)
    {
        $this->query = $request->get('query', '');
        $this->language = $request->get('language', '');
        $this->sortBy = $request->get('sort', 'updated');

        if ($user !== null && $user->is(auth()->user())) {
            $this->user = $user;
        }
    }

    /**
     * @return array<string,mixed>
     */
    public function data(): array
    {
        return [
            'query' => $this->query,
            'language' => $this->language,
            'languageOptions' => ContentVersion::getTranslatedUsedLocales($this->user),
            'sortBy' => $this->sortBy,
            'sortOptions' => [
                'updated' => trans('messages.sort-last-updated'),
                'created' => trans('messages.sort-last-created'),
            ],
        ];
    }

    public function content(): Builder
    {
        if ($this->user !== null) {
            $content = Content::findForUser($this->user, $this->query);
        } else {
            $content = Content::findShared($this->query);
        }

        return $content
            ->when($this->language, fn (Builder $query) => $query->where('language_iso_639_3', $this->language))
            ->orderBy(match ($this->sortBy) {
                'created' => 'created_at',
                default => 'updated_at',
            }, 'desc')
        ;
    }

    public function contentView(): View
    {
        return view('content.hx-content', [
            'contents' => $this->content()->paginate(),
            'layout' => session()->get('contentLayout', 'grid'),
            'showDrafts' => $this->user !== null,
            'titlePreviews' => session()->has('lti'),
            'hasQuery' => !empty($this->query),
            'mine' => $this->user !== null,
        ]);
    }
}
