<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\ContentExclusion;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ContentExclusionController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = $request->integer('perPage', 50);
        if ($perPage <= 0) {
            $perPage = 50;
        }

        $excluded = ContentExclusion::with('content.latestPublishedVersion')
            ->orderByDesc('id')
            ->paginate($perPage, pageName: 'excluded_page')
            ->withQueryString();

        return view('admin.content-exclusions.index', [
            'activeTab' => 'tabExcluded',
            'excluded' => $excluded,
            'hasSearched' => false,
            'results' => collect(),
            'resultsPaginator' => null,
            'searchParams' => [
                'contentId' => '',
                'title' => '',
                'excludeExcluded' => false,
                'perPage' => $perPage,
            ],
            'message' => null,
        ]);
    }

    public function search(Request $request): View
    {
        $searchContentId = trim((string) $request->string('contentId'));
        $searchTitle = trim((string) $request->string('title'));
        $excludeExcluded = $request->boolean('excludeExcluded');
        $perPage = $request->integer('perPage', 50);
        if ($perPage <= 0) {
            $perPage = 50;
        }
        $results = collect();
        $resultsPaginator = null;
        $message = null;

        if ($searchContentId !== '') {
            $ids = collect(explode(',', $searchContentId))
                ->map(fn ($id) => trim($id))
                ->filter()
                ->unique()
                ->values();

            if ($ids->isNotEmpty()) {
                $query = Content::with(['latestPublishedVersion', 'exclusions'])
                    ->whereIn('id', $ids);

                if ($excludeExcluded) {
                    $query->doesntHave('exclusions');
                }

                $results = $query->get();

                if ($results->isEmpty()) {
                    $message = 'Content not found';
                }
            } else {
                $message = 'Content not found';
            }
        } elseif ($searchTitle !== '') {
            $titles = collect(explode(',', $searchTitle))
                ->map(fn ($t) => trim($t))
                ->filter(fn ($t) => mb_strlen($t) >= 3)
                ->unique()
                ->values();

            if ($titles->isNotEmpty()) {
                $query = Content::with(['latestPublishedVersion', 'exclusions'])
                    ->whereHas('latestPublishedVersion', function ($query) use ($titles) {
                        $query->where(function ($q) use ($titles) {
                            foreach ($titles as $title) {
                                $q->orWhere('title', 'ILIKE', '%' . $title . '%'); // @phpstan-ignore argument.type
                            }
                        });
                    });

                if ($excludeExcluded) {
                    $query->doesntHave('exclusions');
                }

                $paginator = $query->paginate($perPage)->withQueryString();
                $results = $paginator->getCollection();
                $resultsPaginator = $paginator;

                if ($results->isEmpty()) {
                    $message = 'Content not found';
                }
            } else {
                $message = 'Content not found';
            }
        }

        $excluded = ContentExclusion::with('content.latestPublishedVersion')
            ->orderByDesc('id')
            ->paginate($perPage, pageName: 'excluded_page')
            ->withQueryString();

        return view('admin.content-exclusions.index', [
            'activeTab' => 'tabFind',
            'excluded' => $excluded,
            'hasSearched' => true,
            'results' => $results,
            'resultsPaginator' => $resultsPaginator,
            'searchParams' => [
                'contentId' => $searchContentId,
                'title' => $searchTitle,
                'excludeExcluded' => $excludeExcluded,
                'perPage' => $perPage,
            ],
            'message' => $message,
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $request->validate([
            'contentIds' => ['required', 'array'],
            'contentIds.*' => ['ulid', 'exists:contents,id'],
            'excludeFrom' => ['required', 'string'],
        ]);

        $added = 0;

        foreach ($request->input('contentIds') as $contentId) {
            try {
                ContentExclusion::create([
                    'content_id' => $contentId,
                    'exclude_from' => $request->input('excludeFrom'),
                    'user_id' => $request->user()?->id,
                ]);
                $added++;
            } catch (UniqueConstraintViolationException) {
                // Already excluded, skip
            }
        }

        return redirect()
            ->route('admin.content-exclusions.index')
            ->with('alert', "$added content(s) excluded.");
    }

    public function exclusionDialog(Content $content, Request $request): View
    {
        if (!$request->header('HX-Request')) {
            abort(400);
        }

        $content->load('exclusions');

        return view('admin.content-exclusions.hx-dialog', [
            'content' => $content,
        ]);
    }

    public function addOne(Content $content, Request $request): RedirectResponse
    {
        $request->validate([
            'excludeFrom' => ['required', 'in:library_translation_update'],
        ]);

        try {
            ContentExclusion::create([
                'content_id' => $content->id,
                'exclude_from' => $request->input('excludeFrom'),
                'user_id' => $request->user()?->id,
            ]);

            return redirect()->back()->with('alert', 'Content excluded.');
        } catch (UniqueConstraintViolationException) {
            return redirect()->back()->with('alert', 'Content is already excluded.');
        }
    }

    public function removeOne(Content $content, Request $request): RedirectResponse
    {
        $request->validate([
            'excludeFrom' => ['required', 'in:library_translation_update'],
        ]);

        ContentExclusion::where('content_id', $content->id)
            ->where('exclude_from', $request->input('excludeFrom'))
            ->delete();

        return redirect()->back()->with('alert', 'Exclusion removed.');
    }

    public function delete(Request $request): RedirectResponse
    {
        $request->validate([
            'contentIds' => ['required', 'array'],
            'contentIds.*' => ['ulid'],
            'excludeFrom' => ['required', 'string'],
        ]);

        $deleted = ContentExclusion::whereIn('content_id', $request->input('contentIds'))
            ->where('exclude_from', $request->input('excludeFrom'))
            ->delete();

        return redirect()
            ->route('admin.content-exclusions.index')
            ->with('alert', "$deleted exclusion(s) removed.");
    }
}
