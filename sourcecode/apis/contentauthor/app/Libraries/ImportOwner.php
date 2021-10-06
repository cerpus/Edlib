<?php


namespace App\Libraries;

use Cache;
use Carbon\Carbon;
use Cerpus\LaravelAuth\Service\CerpusAuthService;

class ImportOwner
{
    protected $id, $name;

    protected $cacheTime;
    protected $cacheName;

    public function __construct($authId, Carbon $cacheTime = null)
    {
        if (!$authId) {
            throw new \Exception("AuthID is required.");
        }

        $this->cacheName = "ImportOwner|$authId";
        $this->cacheTime = $cacheTime;
        if (!$this->cacheTime) {
            $this->cacheTime = now()->addHour();
        }

        if (!$user = Cache::get($this->cacheName)) {
            /** @var CerpusAuthService $auth */
            $auth = app(CerpusAuthService::class);
            // $token = $auth->getClientCredentialsTokenRequest()->execute();
            $idService = $auth->getCreateUserApiService();
            if (!$user = $idService->getIdentity($authId)) {
                throw new \Exception("Unable to fetch user with AuthId $authId");
            }

            Cache::put($this->cacheName, $user, $this->cacheTime);
        }

        $this->setName($user->identity->displayName);
        $this->setId($user->identity->id);
    }

    /**
     * @return mixed
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

}
