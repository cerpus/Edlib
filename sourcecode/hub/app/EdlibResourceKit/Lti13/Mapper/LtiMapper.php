<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti13\Mapper;

use App\EdlibResourceKit\Lti13\LtiMessage;
use App\EdlibResourceKit\Lti13\Mapping\Field;
use App\EdlibResourceKit\Lti13\Mapping\MappingInterface;
use App\EdlibResourceKit\Lti13\Mapping\ReflectionMapping;
use App\EdlibResourceKit\Lti13\Mapping\WriteType;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Validator;
use stdClass;
use function array_keys;
use function array_map;
use function file_get_contents;
use function implode;
use function is_string;
use function json_decode;
use function property_exists;
use function str_replace;

final class LtiMapper
{
    /** @var array<Field> */
    private array $fields;

    public function __construct(
        MappingInterface $mapping = new ReflectionMapping(),
        private readonly Validator $validator = new Validator(),
    ) {
        $this->fields = $mapping->getFields(LtiMessage::class);

        $validator->resolver()->registerRaw(
            file_get_contents(__DIR__ . '/../../../schema/jwt.schema.json'),
        );
        $validator->resolver()->registerRaw(
            file_get_contents(__DIR__ . '/../../../schema/lti_message.schema.json'),
        );
        $validator->resolver()->registerRaw(
            file_get_contents(__DIR__ . '/../../../schema/oidc.schema.json'),
        );
    }

    public function map(string|stdClass $jsonOrData, string $className): LtiMessage
    {
        $data = is_string($jsonOrData) ? json_decode($jsonOrData) : $jsonOrData;
        $result = $this->validator->validate($data, 'https://spec.edlib.com/schema/lti_message.json');
        $error = $result->error();

        if ($error) {
            $fn = function (ValidationError $error) use (&$fn) {
                return [
                    'path' => implode('.', $error->data()->path()),
                    'message' => str_replace(
                        array_map(fn($k) => '{'.$k.'}', array_keys($error->args())),
                        $error->args(),
                        $error->message()
                    ),
                    'subErrors' => array_map($fn, $error->subErrors()),
                ];
            };

            throw new ValidationException($fn($error));
        }

        $constructArgs = [];

        foreach ($this->fields as $field) {
            $write = $field->getWrite();

            if (!property_exists($data, $field)) {
                continue;
            }

            $value = $data->{$field->getClaim()};

            match ($write->getType()) {
                WriteType::ConstructParam => $constructArgs[$write->getName()] = $value,
            };
        }
    }
}
