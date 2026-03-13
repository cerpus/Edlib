<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class ContentFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_index_filters_by_h5p_types()
    {
        config(['scout.driver' => 'collection']);

        $response = $this->get(route('content.index', [
            'type' => ['H5P.DragText', 'H5P.Flashcards']
        ]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_content_index_handles_malformed_type_parameter()
    {
        $response = $this->get(route('content.index', [
            'type' => ['""...".replace("z","o")"']
        ]));
        $response->assertStatus(404);

        // Example payloads from a penetration attempt
        $response = $this->get(route('content.index', [
            'type' => ['"+"A".concat(70-3).concat(22*4).concat(108).concat(88).concat(104).concat(81)+(require"socket" Socket.gethostbyname("hitza"+"bwdoorkva3024.bxss.me.")[3].to_s)+"']
        ]));
        $response->assertStatus(404);

        $response = $this->get(route('content.index', [
            'type' => ['\'"></style></textarea></iframe></script><iframe src="https://hitztihumywvs.bxss.me"></iframe><link rel=attachment href="https://hitztihumywvs.bxss.me">']
        ]));
        $response->assertStatus(404);

        $response = $this->get(route('content.index', [
            'type' => ['Accordion\'"()&%<zzz><ScRiPt >w3af(9928)</ScRiPt>']
        ]));
        $response->assertStatus(404);
    }

    public function test_content_index_handles_type_as_string()
    {
        $response = $this->get(route('content.index', [
            'type' => 'string_value'
        ]));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
