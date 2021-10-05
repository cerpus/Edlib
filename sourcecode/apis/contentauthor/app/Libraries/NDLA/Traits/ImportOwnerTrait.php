<?php


namespace App\Libraries\NDLA\Traits;

use Cache;
use Carbon\Carbon;
use Cerpus\AuthCore\CreateUserApiService;
use Cerpus\LaravelAuth\Service\CerpusAuthService;

trait ImportOwnerTrait
{
    /**
     * @return |null
     */
    protected function getImportOwner()
    {
        $importOwnerAuthId = config('ndla.userId');

        $cacheKey = 'NdlaImportOwner|' . $importOwnerAuthId;
        $cacheTime = Carbon::now()->addHour();

        $owner = Cache::get($cacheKey, null);

        if (!$owner) {
            try {
                /** @var CreateUserApiService $userApi */
                $userApi = resolve(CerpusAuthService::class)->getCreateUserApiService();
                $owner = $userApi->getIdentity($importOwnerAuthId)->identity;
                Cache::put($cacheKey, $owner, $cacheTime);
            } catch (\Throwable $t) {

            }
        }

        return $owner;
    }
}
