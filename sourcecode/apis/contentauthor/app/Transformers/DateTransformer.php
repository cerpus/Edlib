<?php

namespace App\Transformers;


use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class DateTransformer extends TransformerAbstract
{

    public function transform(Carbon $date)
    {
        return [
            'timestamp' => $date->getTimestamp(),
            'datetime' => $date->toDateTimeString(),
            'atom' => $date->toAtomString(),
        ];
    }
}