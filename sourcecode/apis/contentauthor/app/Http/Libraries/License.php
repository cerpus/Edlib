<?php
/**
 * Created by PhpStorm.
 * User: oddaj
 * Date: 8/22/16
 * Time: 11:10 AM
 */

namespace App\Http\Libraries;

use Cerpus\LicenseClient\Contracts\LicenseContract;
use Log;
use Illuminate\Support\Facades\Session;
use App\Libraries\NDLA\Importers\Handlers\Helpers\LicenseHelper;
use stdClass;

class License
{
    use LicenseHelper;

    /** @var bool|LicenseContract  */
    protected $lc = false;

    public function __construct($config = [], $key = null, $secret = null)
    {
        if (config('app.enable_licensing')) {
            $this->lc = resolve(LicenseContract::class);
        }
    }

    public function addContent($contentId, $contentName)
    {
        if ($this->lc === false) {
            return false;
        }

        return $this->lc->addContent($contentId, $contentName);
    }

    public function getOrAddContent($content)
    {
        if ($this->lc === false) {
            return false;
        }

        $theContent = $this->lc->getContent($content->id);
        if ($theContent === false) {
            $theContent = $this->lc->addContent($content->id, $content->title);
        }

        return $theContent;
    }

    /**
     * Return the OER Licenses.
     *
     * @param int $id
     * @return none
     */
    public function getLicenses($ltiRequest = null)
    {
        if ($this->lc === false) {
            return [];
        }
        $allowedLicenses = explode(',', $this->getAllowedLicenses($ltiRequest));
        $licenses = $this->lc->getLicenses();

        $finalLicenses = [];
        foreach ($licenses as $license) {
            if (in_array($license->id, $allowedLicenses)) {
                $finalLicenses[] = $license;
            }
        }

        $finalLicenses = $this->translateLicensesName($finalLicenses);

        return $finalLicenses;
    }

    protected function translateLicensesName($licenses = [])
    {
        return collect($licenses)
            ->map(function ($license) {
                $key = 'licenses.' . $license->id;
                $license->name = trans($key);
                return $license;
            });
    }

    protected function getAllowedLicenses($ltiRequest)
    {
        $allowedLicenses = 'PRIVATE,CC0,PDM,BY,BY-SA,BY-NC,BY-ND,BY-NC-SA,BY-NC-ND,EDLL';
        if (empty($ltiRequest)) {
            $sessionAllowed = Session::get('allowedLicenses', $allowedLicenses);
            return $sessionAllowed;
        }

        return $ltiRequest->getAllowedLicenses($allowedLicenses);
    }

    public function getDefaultLicense($ltiRequest = null)
    {
        $defaultLicense = config('license.default-license');
        if (empty($ltiRequest)) {
            $defaultLicense = Session::get('defaultLicense', $defaultLicense);
            return $defaultLicense;
        }

        return $ltiRequest->getDefaultLicense($defaultLicense);

    }

    public function getLicense($id)
    {
        $license = false;

        if ($this->lc === false) {
            return $license;
        }

        try {
            $content = $this->lc->getContent($id);
            if (property_exists($content, 'licenses') && !empty($content->licenses)) {
                $license = $content->licenses[0];
                // Normalize license
                $license = $this->toEdLibLicenseString($license);
            }
        } catch (\Exception $e) {
            Log::error('Unable to get license for content ' . $id . ': ' . $e->getMessage());
        }

        return $license;
    }

    public function getLicensesByContentId($ids)
    {
        $licenses = [];

        if ($this->lc === false || empty($ids)) {
            return $licenses;
        }

        try {
            $contents = $this->lc->getContents($ids);

            foreach ($ids as $id) {
                $license = false;
                foreach ($contents as $content) {
                    if ($content->content_id == $id && property_exists($content, 'licenses') && !empty($content->licenses)) {
                        $license = $content->licenses[0];
                        // Normalize license
                        $license = $this->toEdLibLicenseString($license->name);
                    }
                }

                $value = new stdClass();
                $value->id = $id;
                $value->license = $license;
                $licenses[] = $value;
            }
        } catch (\Exception $e) {
            Log::error('Unable to get licenses for content ' . json_encode($ids) . ': ' . $e->getMessage());
            throw $e;
        }

        return $licenses;
    }

    public function setLicense($licenseId, $contentId)
    {
        if ($this->lc === false) {
            return false;
        }

        try {
            if ($licenseId !== $this->getLicense($contentId)) {
                return $this->lc->addLicense($contentId, $this->toEdLibLicenseString($licenseId));
            }
        } catch (\Exception $e) {
            Log::error('Unable to add license ' . $licenseId . ' to ' . $contentId . ': ' . $e->getMessage());

            return false;
        }
    }

    public function isContentCopyable($contentId)
    {
        if ($this->lc === false) {
            return false;
        }

        try {
            return $this->lc->isContentCopyable($contentId);
        } catch (\Exception $e) {
            Log::error('Unable to determine if content is copyable. Errormessage : ' . $e->getMessage());
            return false;
        }

    }

    public function isLicenseSupported($license)
    {
        if ($this->lc === false) {
            return true;
        }
        try {
            return $this->lc->isLicenseSupported($license);
        } catch (\Exception $e) {
            Log::error('Unable to determine if the license is suppported. Errormessage : ' . $e->getMessage());
            return false;
        }
    }
}
