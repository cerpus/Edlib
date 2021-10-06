<?php


namespace App\Models\Traits;


interface RecommendableInterface
{
    const RE_ACTION_NOOP = "noop";
    const RE_ACTION_UPDATE_OR_CREATE = "update_or_create";
    const RE_ACTION_REMOVE = "remove";
}
