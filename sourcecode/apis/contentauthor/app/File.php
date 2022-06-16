<?php

namespace App;

use App\Libraries\ContentAuthorStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use App\Libraries\DataObjects\ContentStorageSettings;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class File extends Model
{
    use HasFactory;

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function generatePath()
    {
        $contentAuthorStorage = app(ContentAuthorStorage::class);
        return $contentAuthorStorage->getAssetUrl(sprintf(ContentStorageSettings::ARTICLE_FILE, $this->article->id, $this->name), true);
    }

    public function generateTempPath()
    {
        $contentAuthorStorage = app(ContentAuthorStorage::class);
        return $contentAuthorStorage->getAssetUrl(sprintf(ContentStorageSettings::ARTICLE_FILE, 'tmp', $this->name), true);
    }

    public function moveTempToArticle(Article $article)
    {
        $moved = false;
        $fromFile = sprintf(ContentStorageSettings::ARTICLE_FILE, 'tmp', $this->name);
        $toFile = sprintf(ContentStorageSettings::ARTICLE_FILE, $article->id, $this->name);
        $fromFileExists = Storage::disk()->exists($fromFile);
        if ($fromFileExists) {
            Storage::disk()->move($fromFile, $toFile);
            $article->files()->save($this);
            $moved = true;
        }

        return $moved;
    }

    public static function moveUploadedFileToTmp(UploadedFile $uploadedFile)
    {
        $newFile = self::moveUploadedFileTo($uploadedFile, 'tmp');
        $newFile->save();

        return $newFile;
    }

    public static function addUploadedFileToArticle(UploadedFile $uploadedFile, Article $article)
    {
        $newFile = self::moveUploadedFileTo($uploadedFile, $article->id);
        $article->files()->save($newFile);

        return $newFile;
    }

    private static function moveUploadedFileTo(UploadedFile $file, $path = '')
    {
        $fileExtension = $file->getExtension();
        if (empty($fileExtension)) {
            $fileExtension = $file->guessExtension();
        }

        $fileName = Uuid::uuid4()->toString() . '.' . $fileExtension;

        $filePath = sprintf(ContentStorageSettings::ARTICLE_FILE, $path, $fileName);
        Storage::disk()->put($filePath, file_get_contents($file->getRealPath()));

        $newFile = new File();
        $newFile->name = $fileName;
        $newFile->original_name = $file->getClientOriginalName();
        $newFile->size = $file->getSize();
        $newFile->mime = $file->getMimeType();

        unlink($file->getRealPath());

        return $newFile;
    }

}
