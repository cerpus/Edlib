<?php

namespace App\Http\Controllers\API;

use App\Article;
use App\ArticleCollaborator;
use App\CollaboratorContext;
use App\H5PCollaborator;
use App\H5PContent;
use App\H5PContentsUserData;
use App\H5PResult;
use App\Http\Controllers\Controller;
use App\Link;
use App\QuestionSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Ramsey\Uuid\Uuid;

class GdprSubjectDataController extends Controller
{
    protected $lastItemId;
    protected $lastItemType;

    protected function questionDataForQuestionSet($namePrefix, $qs)
    {
        $dataItems = [];
        $dataItems[] = ['name' => $namePrefix.'QuestionSetId', 'type' => 'RELATED', 'value' => $qs->id, 'description' => 'User owns a link wih this ID.'];
        $dataItems[] = ['name' => $namePrefix.'QuestionSetTitle', 'type' => 'RELATED', 'value' => $qs->title, 'description' => 'User owns a link wih this title.'];
        foreach ($qs->questions as $question) {
            $dataItems[] = ['name' => $namePrefix.'QuestionId', 'type' => 'RELATED', 'value' => $question->id, 'description' => 'Question for set '.$qs->id];
            $dataItems[] = ['name' => $namePrefix.'QuestionText', 'type' => 'RELATED', 'value' => $question->question_text, 'description' => 'Question '.$question->id];
            foreach ($question->answers as $answer) {
                $dataItems[] = ['name' => $namePrefix.'AnswerId', 'type' => 'RELATED', 'value' => $answer->id, 'description' => 'Answer for '.$question->id];
                $dataItems[] = ['name' => $namePrefix.'AnswerText', 'type' => 'RELATED', 'value' => $answer->answer_text, 'description' => 'Answer text for '.$answer->id];
            }
        }
        return $dataItems;
    }

    public function getArticleData($namePrefix, $article)
    {
        $dataItems = [];
        $dataItems[] = ['name' => $namePrefix.'ArticleId', 'type' => 'RELATED', 'value' => $article->id, 'description' => 'User has access to an article wih this ID. The article might be one of several versions of the same article.'];
        $dataItems[] = ['name' => $namePrefix.'ArticleTitle', 'type' => 'RELATED', 'value' => $article->title, 'description' => 'User has access to an article wih this title. The article might be one of several versions of the same article.'];
        $dataItems[] = ['name' => $namePrefix.'ArticleContent', 'type' => 'RELATED', 'value' => $article->content, 'description' => 'User has access to an article wih this content. The article might be one of several versions of the same article.'];
        return $dataItems;
    }

    public function getUserArticles($userId, $prevItem=null)
    {
        $dataItems = [];
        $articles = Article::where('owner_id', $userId)->orderBy('id', 'ASC')->limit(5);
        if ($prevItem !== null) {
            $articles = $articles->where('id', '>', $prevItem);
        }
        foreach ($articles->get() as $article) {
            $this->lastItemId = $article->id;
            $dataItems = array_merge($dataItems, $this->getArticleData('owned', $article));
        }
        return $dataItems;
    }

    public function getArticlesByEmail($email, $prevItem=null)
    {
        $dataItems = [];
        $articleCollaborators = ArticleCollaborator::where('email', $email)->orderBy('article_id', 'ASC')->limit(5);
        if ($prevItem !== null) {
            $articleCollaborators = $articleCollaborators->where('article_id', '>', $prevItem);
        }
        foreach ($articleCollaborators->get() as $articleCollaborator) {
            $this->lastItemId = $articleCollaborator->article_id;
            $article = $this->getArticleById($articleCollaborator->article_id);
            $dataItems = array_merge($dataItems, $this->getArticleData('collaborator', $article));
        }
        return $dataItems;
    }

    public function getH5pData($namePrefix, $h5p)
    {
        $dataItems = [];
        $dataItems[] = ['name' => $namePrefix.'H5pId', 'type' => 'RELATED', 'value' => $h5p->id, 'description' => 'User has access to an H5P wih this ID. The H5P might be one of several versions of the same H5P.'];
        $dataItems[] = ['name' => $namePrefix.'H5pTitle', 'type' => 'RELATED', 'value' => $h5p->title, 'description' => 'User has access to an H5P wih this title. The H5P might be one of several versions of the same H5P.'];
        return $dataItems;
    }
    public function getH5ps($userId, $prevItem=null)
    {
        $dataItems = [];
        $h5ps = H5PContent::where('user_id', $userId)->orderBy('id', 'ASC')->limit(5);
        if ($prevItem !== null) {
            $h5ps = $h5ps->where('id', '>', $prevItem);
        }
        foreach ($h5ps->get() as $h5p) {
            $this->lastItemId = $h5p->id;
            $dataItems = array_merge($dataItems, $this->getH5pData('owned', $h5p));
        }
        return $dataItems;
    }
    public function getH5psByEmail($email, $prevItem=null)
    {
        $dataItems = [];
        $h5ps = H5PCollaborator::where('email', $email)->orderBy('h5p_id', 'ASC')->limit(5);
        if ($prevItem !== null) {
            $h5ps = $h5ps->where('h5p_id', '>', $prevItem);
        }
        foreach ($h5ps->get() as $collaborator) {
            $this->lastItemId = $collaborator->h5p_id;
            $h5p = $this->getH5pById($collaborator->h5p_id);
            $dataItems = array_merge($dataItems, $this->getH5pData('collaborator', $h5p));
        }
        return $dataItems;
    }

    public function getLinkData($namePrefix, $link)
    {
        $dataItems = [];
        $dataItems[] = ['name' => $namePrefix.'LinkId', 'type' => 'RELATED', 'value' => $link->id, 'description' => 'User owns a link wih this ID.'];
        $dataItems[] = ['name' => $namePrefix.'LinkTitle', 'type' => 'RELATED', 'value' => $link->title, 'description' => 'User owns a link wih this title.'];
        $dataItems[] = ['name' => $namePrefix.'LinkUrl', 'type' => 'RELATED', 'value' => $link->link_url, 'description' => 'User owns a link wih this url.'];
        $dataItems[] = ['name' => $namePrefix.'LinkText', 'type' => 'RELATED', 'value' => $link->link_text, 'description' => 'User owns a link wih this link text.'];
        return $dataItems;
    }
    public function getLinks($userId, $prevItem=null)
    {
        $dataItems = [];
        $links = Link::where('owner_id', $userId)->orderBy('id', 'ASC')->limit(5);
        if ($prevItem !== null) {
            $links = $links->where('id', '>', $prevItem);
        }
        foreach ($links->get() as $link) {
            $this->lastItemId = $link->id;
            $dataItems = array_merge($dataItems, $this->getLinkData('owned', $link));
        }
        return $dataItems;
    }

    public function getCollaboratorContexts($userId, $prevItem=null)
    {
        $dataItems = [];
        $collaboratorContexts = function () use ($userId) {
            return CollaboratorContext::where('collaborator_id', $userId)->orderBy('context_id', 'ASC')->orderBy('content_id', 'ASC')->limit(5);
        };
        if ($prevItem !== null) {
            $prevItem = json_decode(base64_decode($prevItem), true);
            $collaboratorContextQuery = $collaboratorContexts()->where('context_id', $prevItem['context_id'])->where('content_id', '>', $prevItem['content_id']);
            $array = [];
            foreach ($collaboratorContextQuery->get() as $collaboratorContext) {
                $array[] = $collaboratorContext;
            }
            if (!$array) {
                $collaboratorContextQuery = $collaboratorContexts()->where('context_id', '>', $prevItem['context_id']);
                foreach ($collaboratorContextQuery->get() as $collaboratorContext) {
                    $array[] = $collaboratorContext;
                }
            }
            $collaboratorContexts = $array;
        } else {
            $collaboratorContexts = $collaboratorContexts()->get();
        }
        foreach ($collaboratorContexts as $collaboratorContext) {
            $this->lastItemId = base64_encode(json_encode(['context_id' => $collaboratorContext->context_id, 'content_id' => $collaboratorContext->content_id]));
            $dataItems[] = ['name' => 'collaboratorOnContentId', 'type' => 'RELATED', 'value' => $collaboratorContext->content_id, 'description' => 'Collaborator access via '.$collaboratorContext->context_id.' on system '.$collaboratorContext->system_id.'. Access type '.$collaboratorContext->type.' since '.$collaboratorContext->timestamp.'.'];
            $dataItems = array_merge($dataItems, $this->processItemById('collaborator', $collaboratorContext->content_id));
        }
        return $dataItems;
    }

    public function getQuestionSets($userId, $prevItem=null)
    {
        $dataItems = [];
        $questionsets = QuestionSet::where('owner', $userId)->orderBy('id', 'ASC')->limit(5);
        if ($prevItem !== null) {
            $questionsets = $questionsets->where('id', '>', $prevItem);
        }
        foreach ($questionsets->get() as $qs) {
            $this->lastItemId = $qs->id;
            $dataItems = array_merge($dataItems, $this->questionDataForQuestionSet('owned', $qs));
        }
        return $dataItems;
    }

    public function getH5pUserData($userId, $prevItem=null)
    {
        $dataItems = [];
        $h5pUserDatas = H5PContentsUserData::where('user_id', $userId)->orderBy('id', 'ASC')->limit(5);
        if ($prevItem !== null) {
            $h5pUserDatas = $h5pUserDatas->where('id', '>', $prevItem);
        }
        foreach ($h5pUserDatas->get() as $h5pUserData) {
            $this->lastItemId = $h5pUserData->id;
            $dataItems[] = ['name' => 'h5pUserDataId', 'type' => 'RELATED', 'value' => $h5pUserData->id, 'description' => 'Data container ID for H5P arbritrary user data.'];
            $dataItems[] = ['name' => 'h5pUserDataDataId', 'type' => 'RELATED', 'value' => $h5pUserData->data_id, 'description' => 'Data container '.$h5pUserData->id.' Data ID for H5P arbritrary user data.'];
            $dataItems[] = ['name' => 'h5pUserDataContentId', 'type' => 'RELATED', 'value' => $h5pUserData->content_id, 'description' => 'Content ID for H5P data container '.$h5pUserData->id.'.'];
            $dataItems[] = ['name' => 'h5pUserDataSubContentId', 'type' => 'RELATED', 'value' => $h5pUserData->sub_content_id, 'description' => 'Sub content ID for H5P data container '.$h5pUserData->id.'.'];
            $dataItems[] = ['name' => 'h5pUserData', 'type' => 'RELATED', 'value' => $h5pUserData->data, 'description' => 'Sub content ID for H5P data container '.$h5pUserData->id.'.'];
        }
        return $dataItems;
    }

    public function getH5pResults($userId, $prevItem=null)
    {
        $dataItems = [];
        $h5pResults = H5PResult::where('user_id', $userId)->orderBy('id', 'ASC')->limit(5);
        if ($prevItem !== null) {
            $h5pResults = $h5pResults->where('id', '>', $prevItem);
        }
        foreach ($h5pResults->get() as $h5pResult) {
            $this->lastItemId = $h5pResult->id;
            $dataItems[] = ['name' => 'h5pResultId', 'type' => 'RELATED', 'value' => $h5pResult->id, 'description' => 'H5P Result object'];
            $dataItems[] = ['name' => 'h5pResultContentId', 'type' => 'RELATED', 'value' => $h5pResult->content_id, 'description' => 'Content ID for H5P result object'];
            $dataItems[] = ['name' => 'h5pResultScore', 'type' => 'RELATED', 'value' => $h5pResult->score, 'description' => 'Score for H5P result object'];
            $dataItems[] = ['name' => 'h5pResultMaxScore', 'type' => 'RELATED', 'value' => $h5pResult->max_score, 'description' => 'Max score for H5P result object'];
            $dataItems[] = ['name' => 'h5pResultOpened', 'type' => 'RELATED', 'value' => date('c', $h5pResult->opened), 'description' => 'Opened time for H5P result object'];
            $dataItems[] = ['name' => 'h5pResultFinished', 'type' => 'RELATED', 'value' => date('c', $h5pResult->finished), 'description' => 'Finished time for H5P result object'];
            $dataItems = array_merge($dataItems, $this->processItemById('h5pResult', $h5pResult->content_id));
        }
        return $dataItems;
    }

    public function processItemById($namePrefix, $id)
    {
        $processMethod = function ($func) use ($namePrefix) {
            return function ($item) use ($func, $namePrefix) {
                return $func($namePrefix, $item);
            };
        };
        if (Uuid::isValid($id)) {
            return $this->resolveItemById([
                [[$this, 'getArticleById'], $processMethod([$this, 'getArticleData'])],
                [[$this, 'getLinkById'], $processMethod([$this, 'getLinkData'])],
                [[$this, 'getQuestionSetById'], $processMethod([$this, 'questionDataForQuestionSet'])],
            ], $id);
        } else {
            $h5p = $this->getH5pById($id);
            if ($h5p) {
                return $this->getH5pData($namePrefix, $h5p);
            } else {
                return [];
            }
        }
    }

    public function getH5pById($id)
    {
        return H5PContent::find($id);
    }

    public function getArticleById($id)
    {
        return Article::find($id);
    }

    public function getLinkById($id)
    {
        return Link::find($id);
    }

    public function getQuestionSetById($id)
    {
        return QuestionSet::find($id);
    }

    public function resolveItemById($sourcesAndProcessors, $id)
    {
        foreach ($sourcesAndProcessors as list($source, $processor)) {
            $item = $source($id);
            if ($item) {
                return $processor($item);
            }
        }
        return [];
    }

    public function multiSourcePagination($sourceMap, $prevId=null, $prevType=null)
    {
        if ($prevType !== null) {
            $querying = false;
        } else {
            $querying = true;
        }
        foreach ($sourceMap as $sourceName => $source) {
            $prevIdForType = null;
            if (!$querying && $prevType === $sourceName) {
                $prevIdForType = $prevId;
                $querying = true;
            }
            if ($querying) {
                $output = $source($prevIdForType);
                if ($output) {
                    $this->lastItemType = $sourceName;
                    return $output;
                }
            }
        }
        $this->lastItemId = null;
        $this->lastItemType = null;
        return [];
    }

    public function getUserData(Request $request, $userId)
    {
        $currentUserId = Session::get('authId', null);
        $authAdmin = Session::get('authAdmin', false);
        if ($currentUserId !== $userId && !$authAdmin) {
            \App::abort(403);
        }

        $lastItemType = $request->get('lastType', null);
        $lastItemId = $request->get('lastId', null);
        $dataItems = $this->multiSourcePagination([
            'articles' => function ($prevId) use ($userId) {
                return $this->getUserArticles($userId, $prevId);
            },
            'h5ps' => function ($prevId) use ($userId) {
                return $this->getH5ps($userId, $prevId);
            },
            'links' => function ($prevId) use ($userId) {
                return $this->getLinks($userId, $prevId);
            },
            'questionsets' => function ($prevId) use ($userId) {
                return $this->getQuestionSets($userId, $prevId);
            },
            'collaboratorcontexts' => function ($prevId) use ($userId) {
                return $this->getCollaboratorContexts($userId, $prevId);
            },
            'h5puserdata' => function ($prevId) use ($userId) {
                return $this->getH5pUserData($userId, $prevId);
            },
            'h5presults' => function ($prevId) use ($userId) {
                return $this->getH5pResults($userId, $prevId);
            },
        ], $lastItemId, $lastItemType);
        $response = [
            'items' => $dataItems,
        ];
        if ($this->lastItemType !== null && $this->lastItemId !== null) {
            $nextRoute = route('gdpr.user.data', ['userId' => $userId, 'lastType' => $this->lastItemType, 'lastId' => $this->lastItemId]);
            $response['next'] = $nextRoute;
        }
        return $response;
    }

    public function getUserDataByEmail(Request $request, $email = null)
    {
        $email = $email !== null ? $email : $request->get('email', null);

        $authAdmin = Session::get('authAdmin', false);
        if (!$authAdmin) {
            \App::abort(403);
        }

        $lastItemType = $request->get('lastType', null);
        $lastItemId = $request->get('lastId', null);
        $dataItems = $this->multiSourcePagination([
            'articles' => function ($prevId) use ($email) {
                return $this->getArticlesByEmail($email, $prevId);
            },
            'h5ps' => function ($prevId) use ($email) {
                return $this->getH5psByEmail($email, $prevId);
            }
        ], $lastItemId, $lastItemType);
        $response = [
            'items' => $dataItems
        ];
        if ($this->lastItemType !== null && $this->lastItemId !== null) {
            $nextRoute = route('gdpr.user.data.byemail', ['email' => $email, 'lastType' => $this->lastItemType, 'lastId' => $this->lastItemId]);
            $response['next'] = $nextRoute;
        }
        return $response;
    }
}
