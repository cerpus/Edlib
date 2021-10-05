<?php

return [
    "adapters" => [
        "questionbankservice" => [
            "handler" => \Cerpus\QuestionBankClient\Adapters\QuestionBankAdapter::class,
            "base-url" => env('QUESTIONBANK_SERVICE_URL'),
        ],
    ],
];