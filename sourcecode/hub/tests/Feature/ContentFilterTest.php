<?php

namespace Tests\Feature;

use App\Http\Requests\ContentFilter;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ContentFilterTest extends TestCase
{
    public function testContentFilterValidation(): void
    {
        $request = new ContentFilter();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);
        $this->assertTrue($validator->passes());

        // Type parameter
        $validator = Validator::make(['type' => ['H5P.DragText', 'H5P.Flashcards', 'text', 'something else', 'something   different']], $rules);
        $this->assertTrue($validator->passes());

        $validator = Validator::make(['type' => ["string\nvalue"]], $rules);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['type' => ["string\tvalue"]], $rules);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['type' => 'string_value'], $rules);
        $this->assertTrue($validator->fails());

        // Some payloads from a penetration attempt
        $validator = Validator::make(['type' => ['""...".replace("z","o")"']], $rules);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['type' => ['+A' . chr(70 - 3) . chr(22 * 4) . chr(108) . chr(88) . chr(104) . chr(81) . 'require"socket" Socket.gethostbyname("hitza"+"bwdoorkva3024.bxss.me.")[3].to_s+"']], $rules);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['type' => ['\'"></style></textarea></iframe></script><iframe src="https://hitztihumywvs.bxss.me"></iframe><link rel=attachment href="https://hitztihumywvs.bxss.me">']], $rules);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['type' => ['Accordion\'"()&%<zzz><ScRiPt >w3af(9928)</ScRiPt>']], $rules);
        $this->assertTrue($validator->fails());

        // Sort parameter
        $validator = Validator::make(['sort' => ''], $rules);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['sort' => null], $rules);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['sort' => 'not valid'], $rules);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['sort' => ['updated']], $rules);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['sort' => 'created'], $rules);
        $this->assertTrue($validator->passes());
    }

    public function testContentIndexReturns404OnFailingValidation(): void
    {
        $response = $this->getJson(route('content.index', ['type' => 'string_value']));
        $response->assertStatus(404);

        $response = $this->getJson(route('content.index', ['sort' => '']));
        $response->assertStatus(404);
    }
}
