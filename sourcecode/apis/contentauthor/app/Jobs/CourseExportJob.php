<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use App\CourseExport;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use function GuzzleHttp\Psr7\str;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Traits\HandlesHugeSubjectMemoryLimit;
use App\Libraries\NDLA\Exporters\EdStepExport;
use App\Libraries\Auth\Traits\ClientCredentialsHelper;

class CourseExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ClientCredentialsHelper, HandlesHugeSubjectMemoryLimit;

    public $subjectId, $topicId;

    public $timeout = 3600; //One hour for the EdStep export job.
    public $tries = 1;

    /** @var EdStepExport */
    public $courseExporter;

    /** @var CourseExport */
    public $courseLog;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($subjectId, $topicId)
    {
        $this->subjectId = $subjectId;
        $this->topicId = $topicId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        set_time_limit($this->timeout);
        $this->expandMemoryLimitForHugeSubjects($this->subjectId);

        $this->courseExporter = resolve(EdStepExport::class);
        $this->courseLog = CourseExport::byNdlaId($this->topicId);

        $subject = $this->courseExporter->processSubject($this->subjectId);

        $theCourse = null;
        $topicId = strtolower($this->topicId);
        foreach ($subject->courses as $course) {
            if (strtolower($course->id) === $topicId) {
                $theCourse = $course;
                break;
            }
        }

        if ($theCourse) {
            $client = resolve(Client::class);

            $form = [
                'data' => json_encode($theCourse)
            ];

            $message = "Default course export message.";

            try {
                $response = $client->request('POST', '/api/v1/import/ndla', [
                    'base_uri' => config('ndla.edStepUrl'),
                    'headers' => [
                        'Authorization' => "Bearer " . $this->getClientCredentialsAccessToken(),
                    ],
                    'form_params' => $form,
                    'timeout' => $this->timeout,
                ]);

                $result = json_decode($response->getBody()->getContents());
                $message = "Export of the course <strong>\"{$theCourse->title}\"</strong> was a success! Created {$result->modules} modules and {$result->activities} activities. <a href=\"{$result->edit}\" class=\"alert-link\" target=\"_blank\">Edit Course</a>";
                $this->courseLog->edstep_id = $result->id ?? null;
                $this->courseLog->edit_url = $result->edit ?? null;
                $this->courseLog->modules_created = $result->modules ?? 0;
                $this->courseLog->activities_created = $result->activities ?? 0;
                $this->courseLog->message = $message;
                $this->courseLog->save();
            } catch (ClientException $e) {
                if ($e->hasResponse()) {
                    $response = $e->getResponse();
                    $responseText = str($response);
                    $message .= "Import failed(1). Got error from EdStep. {$response->getStatusCode()} {$response->getReasonPhrase()}: {$responseText}.";
                }

                $this->courseLog->message = $message;
                $this->courseLog->save();

                Log::error($message);

                throw $e;
            } catch (RequestException $e) {
                $message .=  "Import failed(4). RequestException. {$e->getCode()} {$e->getMessage()}";

                throw $e;
            } catch (\Throwable $t) {
                $message .= "Import failed(2). Got error from EdStep. {$t->getCode()}: {$t->getMessage()}";

                $this->courseLog->message = $message;
                $this->courseLog->save();

                Log::error($message);

                throw $t;
            }
        } else {
            $message = "Import failed(3). Unable to find topic {$this->topicId} in subject {$this->subjectId}.";

            $this->courseLog->message = $message;
            $this->courseLog->save();

            Log::error($message);
        }

    }
}
