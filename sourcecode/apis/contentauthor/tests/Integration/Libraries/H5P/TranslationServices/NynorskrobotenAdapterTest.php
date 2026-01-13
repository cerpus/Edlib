<?php

declare(strict_types=1);

namespace Tests\Integration\Libraries\H5P\TranslationServices;

use App\Libraries\H5P\Dataobjects\H5PTranslationDataObject;
use App\Libraries\H5P\TranslationServices\NynorskrobotenAdapter;
use GuzzleHttp\Client;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('live_integration')]
final class NynorskrobotenAdapterTest extends TestCase
{
    public function testIsUp(): void
    {
        $client = new Client([
            'base_uri' => config('services.nynorskroboten.domain'),
        ]);
        $response = $client->get('up');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Service is up.', $response->getBody()->getContents());
    }

    public function testActualTranslation(): void
    {
        $adapter = app(NynorskrobotenAdapter::class);

        $data = new H5PTranslationDataObject([
            "text-01" => "<p>En tekst på <b>bokmål</b> som brukes for å teste automatisk oversettelse til <b>nynorsk</b></p>",
            "text-12" => "<p><p>Det blåser ikke på banen når en spiller innendørs</p><p>Det er en mengde foreløpige etterspørsler på brøyting av skøytebaner</p></p>",
        ], 'nob');

        $translated = $adapter->translate('nno', $data);

        $this->assertSame([
            "text-01" => "<p>Ein tekst på <b>bokmål</b> som blir brukt for å teste automatisk omsetjing til <b>nynorsk</b></p>",
            "text-12" => "<p></p><p>Det blåser ikkje på banen når ein spelar innandørs</p><p>Det er ei mengde foreløpige etterspørselar på brøyting av skøytebanar</p>",
        ], $translated->getFields());
    }
}
