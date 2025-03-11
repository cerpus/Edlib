<?php

namespace App\Libraries\H5P\Reports;

use App\Exceptions\UnknownH5PPackageException;
use App\H5PContentsUserData;
use App\Libraries\H5P\Helper\H5PPackageProvider;
use App\Libraries\H5P\Interfaces\PackageInterface;

class H5PPackage
{
    public function questionsAndAnswers(array $contexts, $userId): array
    {
        return H5PContentsUserData::where("user_id", $userId)
            ->ofContexts($contexts)
            ->oldest('id')
            ->get()
            ->keyBy('context')
            ->values()
            ->map(function (H5PContentsUserData $userData) {
                $content = $userData->content()->first();
                $library = $content->library()->first();
                try {
                    /** @var PackageInterface $package */
                    $package = H5PPackageProvider::make($library->name, $content->parameters);
                    if ($package->canExtractAnswers() === true) {
                        return (object) [
                            'package' => $package,
                            'answers' => $userData->getData(),
                            'context' => $userData->context,
                        ];
                    }
                } catch (UnknownH5PPackageException $e) {
                }
            })->reject(function ($content) {
                return is_null($content) || !$content->package->validate();
            })->map(function ($content) {
                $content->package->getPackageAnswers($content->answers);
                $content->package->setAnswers($content->answers);
                return [
                    'elements' => $content->package->getElements(),
                    'context' => $content->context,
                ];
            })->toArray();
    }
}
