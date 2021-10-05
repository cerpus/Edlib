<?php
namespace App\Http\Controllers\API;

use Log;
use App\Article;
use App\H5PContent;
use App\Events\H5PWasCopied;
use App\Events\ArticleWasCopied;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiCopyRequest;
use Cerpus\VersionClient\VersionData;

class ContentCopyController extends Controller
{
    protected $messages = [];

    /**
     * Copy resorce and return new ID
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ApiCopyRequest $request)
    {

        $contentId = $request->get('id');
        $newOwnerId = $request->get('auth_id');

        $content = Article::find($contentId);
        if (!$content) {
            $content = H5PContent::findOrFail($contentId);
        }

        if (!$this->checkForAccess($newOwnerId, $content)) {
            return response()->json(['created' => false, 'messages' => $this->messages], 403);
        }

        try {
            $className = get_class($content);
            if ($className === 'App\Article') {
                $newArticle = $content->makeCopy($newOwnerId);
                if ($newArticle) {
                    event(new ArticleWasCopied($newArticle, VersionData::COPY));

                    return response(['created' => true, 'id' => $newArticle->id], 201);
                }
            } else if ($className == 'App\H5PContent') {
                $newH5P = $content->makeCopy($newOwnerId);
                if ($newH5P) {
                    event(new H5PWasCopied($content, $newH5P, VersionData::COPY));

                    return response(['created' => true, 'id' => $newH5P->id], 201);
                }
            }
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ': ' . $e->getCode() . ' ' . $e->getMessage());
        }

        return response(['created' => false], 500);
    }

    protected function checkForAccess($newOwner, $content)
    {
        $errors = [];
        if ($content->isOwner($newOwner)) {
            return true;
        }
        $errors[] = "User '$newOwner' is not the owner.";

        if ($content->isCollaborator()) {
            return true;
        }
        $errors[] = "User '$newOwner' is not a collaborator.";

        $this->messages[] = $errors;

        return false;
    }

}
