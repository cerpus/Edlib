<?php


namespace App\Libraries\H5P\Helper;


use App\H5POption;
use Illuminate\Support\Facades\Cache;

class H5POptionsCache
{
    private $cachedOptions = [];
    private $cacheName = "H5POptions";
    private $cacheTime = 60; // Keep cache for one minute

    public function get($name, $default = null)
    {
        if (empty($this->cachedOptions)) {
            $this->fresh();
        }

        return $this->cachedOptions[$name] ?? $default;
    }

    public function fresh()
    {
        $this->cachedOptions = H5POption::all()
            ->mapWithKeys(function ($option) {
                return [$option->option_name => $option->option_value];
            })->all();

        Cache::put($this->cacheName, $this->cachedOptions, now()->addSeconds($this->cacheTime));
    }
}
