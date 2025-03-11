<?php

namespace App\Libraries\H5P;

use App\Libraries\H5P\Interfaces\ProgressInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;

class H5PProgress implements ProgressInterface
{
    /**
     * @var $db PDO
     */
    private $db;

    private $currentUserId;

    private $tableName = 'h5p_contents_user_data';

    private $request;

    public function __construct($db, $userId)
    {
        $this->db = $db;
        $this->currentUserId = $userId;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function storeProgress(Request $request)
    {
        switch ($request->get('action')) {
            case "h5p_setFinished":
                return $this->storeFinished($request->all());
            case "h5p_contents_user_data":
                return $this->storeUserContentData($request->all());
            case "h5p_preview":
                return ['success' => true];
            default:
                throw new \Exception("Invalid action.");
        }
    }

    protected function storeFinished(array $requestValues)
    {
        if ($this->processFinished($requestValues) !== true) {
            \H5PCore::ajaxError("Error");
            exit();
        }
        \H5PCore::ajaxSuccess();
        exit();
    }

    protected function storeUserContentData(array $requestValues)
    {
        $response = new \stdClass();
        $response->success = false;
        try {
            $contentId = $requestValues['content_id'];
            $dataId = $requestValues['data_type'];
            $subContentId = $requestValues['sub_content_id'];
            $data = $requestValues['data'];
            $preload = $requestValues['preload'];
            $invalidate = $requestValues['invalidate'];
        } catch (\Exception $e) {
            $response->message = "Missing parameters";
            return $response; // Missing parameters or not logged in user...
        }
        $context = array_key_exists("context", $requestValues) ? $requestValues['context'] : null;

        if ($data !== null && $preload !== null && $invalidate !== null) {
            if ($data === '0') {
                $response->message = "Deleting.";
                $response->success = $this->deleteRow($contentId, $dataId, $subContentId, $context);
            } else {
                // Wash values to ensure 0 or 1.
                $preload = ($preload === '0' ? 0 : 1);
                $invalidate = ($invalidate === '0' ? 0 : 1);

                if ($this->shouldUpdate($contentId, $dataId, $subContentId, $context)) {
                    // Update data
                    $response->message = "Updating";
                    $response->success = $this->updateUserProgress(
                        $data,
                        $preload,
                        $invalidate,
                        $contentId,
                        $dataId,
                        $subContentId,
                        $context,
                    );
                } else {
                    // Insert new data
                    $response->message = "Inserting";
                    $response->success = $this->insertUserProgress(
                        $contentId,
                        $subContentId,
                        $dataId,
                        $data,
                        $preload,
                        $invalidate,
                        $context,
                    );
                }
            }
        }
        return $response;
    }

    /**
     * Get the progress
     *
     * @return bool|string  False if no data
     */
    public function getProgress(Request $request)
    {
        if ($this->currentUserId !== false) {
            $sql = "SELECT data FROM h5p_contents_user_data WHERE";
            $sql .= " content_id = :contentId AND";
            $sql .= " user_id = :user AND";
            $sql .= " sub_content_id = :subContentId AND";
            $sql .= " data_id = :data_type AND";

            $params = [
                ':contentId' => $request->get('content_id'),
                ':user' => $this->currentUserId,
                ':subContentId' => $request->get("sub_content_id"),
                ':data_type' => $request->get("data_type"),
            ];

            if (!is_null($request->get("context"))) {
                $sql .= " context = :context";
                $params[':context'] = $request->get("context");
            } else {
                $sql .= " context IS NULL";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $result = $stmt->fetchColumn();
            if ($result !== false) {
                return $result;
            }
        }

        return null;
    }

    public function getState($contentId, $context)
    {
        $sql = "select data, sub_content_id, data_id from $this->tableName where content_id=:contentId and user_id=:userId and preload=1";
        $params = [
            ':contentId' => $contentId,
            ':userId' => $this->currentUserId,
        ];

        if (!is_null($context)) {
            $sql .= ' and context = :context';
            $params[':context'] = $context;
        } else {
            $sql .= ' and context IS NULL';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetchAll(\PDO::FETCH_OBJ);
        if ($res) {
            $userData = [];
            foreach ($res as $result) {
                $userData[$result->sub_content_id][$result->data_id] = $result->data;
            }
            return $userData;
        }
        return false;
    }

    public function deleteProgressForId($id)
    {
        $sql = "delete from $this->tableName where content_id=:id";
        $params = [
            ':id' => $id,
        ];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }


    private function deleteRow($content_id, $data_id, $sub_content_id, $context)
    {
        $sql = "delete from $this->tableName where content_id=:content_id and data_id=:data_id and user_id=:user_id and sub_content_id=:sub_content_id";
        $params = [
            ':content_id' => $content_id,
            ':data_id' => $data_id,
            ':user_id' => $this->currentUserId,
            ':sub_content_id' => $sub_content_id,
        ];
        if (is_null($context)) {
            $sql .= " and context IS NULL";
        } else {
            $sql .= " and context = :context";
            $params[':context'] = $context;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }


    private function shouldUpdate($content_id, $data_id, $sub_content_id, $context)
    {
        $params = [
            ':content_id' => $content_id,
            ':user_id' => $this->currentUserId,
            ':data_id' => $data_id,
            ':sub_content_id' => $sub_content_id,
            ':context' => $context,
        ];

        $sql = "select context from $this->tableName where content_id=:content_id and user_id=:user_id and data_id=:data_id and sub_content_id=:sub_content_id and (context = :context OR context IS NULL)";
        /** @var PDOStatement $stmt */
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);

        if (count($rows) > 0) {
            foreach ($rows as $row) {
                if ($row === $context) {
                    return true;
                }
            }
        }
        return false;
    }


    private function updateUserProgress($data, $preload, $invalidate, $content_id, $data_id, $sub_content_id, $context)
    {
        $sql = "update $this->tableName
          set data=:data, preload=:preload, invalidate=:invalidate, updated_at=:now
          where user_id=:user_id and content_id=:content_id and data_id=:data_id and sub_content_id=:sub_content_id";
        $params = [
            ':data' => $data,
            ':preload' => $preload,
            ':invalidate' => $invalidate,
            ':user_id' => $this->currentUserId,
            ':content_id' => $content_id,
            ':data_id' => $data_id,
            ':sub_content_id' => $sub_content_id,
            ':now' => Carbon::now(),
        ];

        if (!is_null($context)) {
            $sql .= " and context = :context";
            $params[':context'] = $context;
        } else {
            $sql .= " and context IS NULL";
        }

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }


    private function insertUserProgress($content_id, $sub_content_id, $data_id, $data, $preload, $invalidate, $context)
    {
        $sql = "insert into $this->tableName (user_id, content_id, sub_content_id, data_id, data, preload, invalidate, updated_at, context)
                values(:user_id, :content_id, :sub_content_id, :data_id, :data, :preload, :invalidate, :now, :context)";
        $params = [
            'user_id' => $this->currentUserId,
            'content_id' => $content_id,
            'sub_content_id' => $sub_content_id,
            'data_id' => $data_id,
            'data' => $data,
            'preload' => $preload,
            'invalidate' => $invalidate,
            'now' => Carbon::now(),
            'context' => $context,
        ];
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function countProgresses($id)
    {
        $sql = "select count(content_id) as progresses from $this->tableName where content_id=:id";
        $params = [
            ':id' => $id,
        ];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch(\PDO::FETCH_OBJ);
        return $res->progresses;
    }


    protected function processFinished(array $requestValues)
    {
        $content_id = @filter_var($requestValues['contentId'], FILTER_VALIDATE_INT);
        if (!$content_id) {
            return false;
        }

        $data = [
            'score' => @$requestValues['score'],
            'max_score' => @$requestValues['maxScore'],
            'opened' => @$requestValues['opened'],
            'finished' => @$requestValues['finished'],
            'time' => @$requestValues['time'],
            'context' => @$requestValues['context'],
        ];
        if (is_null($data['time'])) {
            $data['time'] = 0;
        }
        return (resolve(\H5PFrameworkInterface::class))->handleResult(
            $this->currentUserId,
            $content_id,
            $data['score'],
            $data['max_score'],
            $data['opened'],
            $data['finished'],
            $data['time'],
            $data['context'],
        );
    }
}
