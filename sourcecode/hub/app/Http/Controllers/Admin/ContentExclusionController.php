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
        $excluded = ContentExclusion::with('content.latestPublishedVersion')
            ->orderByDesc('id')
            ->paginate(50, pageName: 'excluded_page');

        return view('admin.content-exclusions.index', [
            'activeTab' => 'tabExcluded',
            'excluded' => $excluded,
            'hasSearched' => false,
            'results' => collect(),
            'resultsPaginator' => null,
            'searchParams' => ['contentId' => '', 'title' => ''],
            'message' => null,
        ]);
    }

    public function search(Request $request): View
    {
        $searchContentId = trim((string) $request->string('contentId'));
        $searchTitle = trim((string) $request->string('title'));
        $results = collect();
        $resultsPaginator = null;
        $message = null;

        if ($searchContentId !== '') {
            $content = Content::with('latestPublishedVersion')
                ->find($searchContentId);

            if ($content) {
                $results = collect([$content]);
            } else {
                $message = 'Content not found';
            }
        } elseif (mb_strlen($searchTitle) >= 3) {
            $paginator = Content::with('latestPublishedVersion')
                ->whereHas('latestPublishedVersion', function ($query) use ($searchTitle) {
                    $query->where('title', 'ILIKE', '%' . $searchTitle . '%'); // @phpstan-ignore argument.type
                })
                ->paginate(25);
            $paginator->appends(['title' => $searchTitle]);
            $results = $paginator->getCollection();
            $resultsPaginator = $paginator;
        }

        $excluded = ContentExclusion::with('content.latestPublishedVersion')
            ->orderByDesc('id')
            ->paginate(50, pageName: 'excluded_page');

        return view('admin.content-exclusions.index', [
            'activeTab' => 'tabFind',
            'excluded' => $excluded,
            'hasSearched' => true,
            'results' => $results,
            'resultsPaginator' => $resultsPaginator,
            'searchParams' => [
                'contentId' => $searchContentId,
                'title' => $searchTitle,
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
