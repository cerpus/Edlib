<?php

namespace App;

use Log;
use Storage;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use App\Libraries\DataObjects\ContentStorageSettings;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class File extends Model
{
    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function generatePath()
    {
        return config('app.useContentCloudStorage') ? route('content.asset', ['path' => sprintf(ContentStorageSettings::ARTICLE_FILE, $this->article->id, $this->name)], false) : config('app.article-public-path') . '/' . $this->article->id . '/' . $this->name;
    }

    public function generateTempPath()
    {
        return config('app.useContentCloudStorage') ? route('content.asset', ['path' => sprintf(ContentStorageSettings::ARTICLE_FILE, 'tmp', $this->name)], false) : config('app.article-public-path') . '/tmp/' . $this->name;
    }

    public function moveTempToArticle(Article $article)
    {
        $moved = false;
        try {
            if( config('app.useContentCloudStorage')){
                $disk = Storage::cloud();
                $fromFile = sprintf(ContentStorageSettings::ARTICLE_FILE, 'tmp', $this->name);
                $toFile = sprintf(ContentStorageSettings::ARTICLE_FILE, $article->id, $this->name);
            } else {
                $disk = Storage::disk('article-uploads');
                $fromFile = 'tmp/' . $this->name;
                $toFile = $article->id . '/' . $this->name;
            }
            $fromFileExists = $disk->exists($fromFile);
            if ($fromFileExists) {
                $disk->move($fromFile, $toFile);
                $article->files()->save($this);
                $moved = true;
            }
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ': ' . $e->getMessage());
            throw $e;
        }
        return $moved;
    }

    public static function moveUploadedFileToTmp(UploadedFile $uploadedFile)
    {
        try {
            $newFile = self::moveUploadedFileTo($uploadedFile, 'tmp');
            $newFile->save();
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ': ' . $e->getMessage());
            throw $e;
        }

        return $newFile;
    }

    public static function addUploadedFileToArticle(UploadedFile $uploadedFile, Article $article)
    {
        try {
            $newFile = self::moveUploadedFileTo($uploadedFile, $article->id);
            $article->files()->save($newFile);

        } catch (\Exception $e) {
            Log::error(__METHOD__ . ': ' . $e->getMessage());
            throw $e;
        }

        return $newFile;
    }

    private static function moveUploadedFileTo(UploadedFile $file, $path = '')
    {
        try {
            $fileExtension = $file->getExtension();
            if (empty($fileExtension)) {
                $fileExtension = $file->guessExtension();
            }

            $fileName = Uuid::uuid4()->toString() . '.' . $fileExtension;

            if( config('app.useContentCloudStorage')){
                $disk = Storage::cloud();
                $filePath = sprintf(ContentStorageSettings::ARTICLE_FILE, $path, $fileName);
            } else {
                $disk = Storage::disk('article-uploads');
                $filePath = $path . '/' . $fileName;
            }
            $disk->put($filePath, file_get_contents($file->getRealPath()));

            $newFile = new File();
            $newFile->name = $fileName;
            $newFile->original_name = $file->getClientOriginalName();
            $newFile->size = $file->getSize();
            $newFile->mime = $file->getMimeType();

            unlink($file->getRealPath());
        } catch (Exception $e) {
            Log::error(__METHOD__ . ': ' . $e->getMessage());
            throw $e;
        }

        return $newFile;
    }

}
