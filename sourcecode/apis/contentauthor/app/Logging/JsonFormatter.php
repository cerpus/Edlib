<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter as BaseJsonFormatter;

/**
 * JSON formatter with stack traces
 */
class JsonFormatter extends BaseJsonFormatter
{
    public function __construct(
        int $batchMode = BaseJsonFormatter::BATCH_MODE_JSON,
        bool $appendNewline = true,
        bool $ignoreEmptyContextAndExtra = false,
    ) {
        parent::__construct($batchMode, $appendNewline, $ignoreEmptyContextAndExtra);

        $this->includeStacktraces = true;
    }
}
