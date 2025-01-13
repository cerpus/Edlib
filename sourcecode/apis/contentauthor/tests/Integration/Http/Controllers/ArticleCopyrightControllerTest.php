<?php

namespace Tests\Integration\Http\Controllers;

use App\Article;
use App\H5PContent;
use App\H5PLibrary;
use App\Http\Controllers\ArticleCopyrightController;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

class ArticleCopyrightControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_copyright_endpointIsWorking()
    {
        $article = Article::factory()->create([
            'title' => 'This is a test article',
            'license' => 'PRIVATE',
        ]);

        $response = $this->get('/article/' . $article->id . '/copyright');
        $response->assertStatus(200);
        $response->assertJson([
            'article' => [
                'license' => 'PRIVATE',
                'attribution' => [
                    'origin' => null,
                    'originators' => [],
                ],
                'assets' => [],
            ],
        ]);
    }

    public function test_copyright_canFetchOriginators()
    {
        $article = Article::factory()->create();
        $article->setAttributionOrigin('http://en.wikipedia.org/');
        $article->addAttributionOriginator('http://www.example.com', 'Source');
        $article->addAttributionOriginator('Luigi', 'Writer');
        $article->license = 'PRIVATE';
        $article->save();

        $response = $this->get('/article/' . $article->id . '/copyright');
        $response->assertStatus(200);
        $response->assertJson([
            'article' => [
                'license' => 'PRIVATE',
                'attribution' => [
                    'origin' => 'http://en.wikipedia.org/',
                    'originators' => [
                        [
                            'name' => 'http://www.example.com',
                            'role' => 'Source',
                        ],
                        [
                            'name' => 'Luigi',
                            'role' => 'Writer',
                        ],
                    ],
                ],
                'assets' => [],
            ],
        ]);
    }

    public function test_copyright_canFetchSubresourceCopyright()
    {
        $h5pContent = H5PContent::factory()->create([
            'library_id' => H5PLibrary::factory()->create()->id,
            'filtered' => '[]',
        ]);

        $article = Article::factory()->create([
            'content' => '<p><iframe class="edlib_resource" height="171" ' .
                'src="/lti/launch?url=http%3A%2F%2Fcore%2Flti%2Flaunch%2F44444444-4444-4444-4444-444444444444">' .
                '</iframe></p>',
            'license' => 'PRIVATE',
        ]);

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn(Utils::streamFor(json_encode([
                'resource' => [
                    'owner' => 'd579f889-dd4a-412d-bb47-9d4ad9523cbd',
                    'tagObjects' => [],
                    'metadata' => [],
                    'created' => $h5pContent->created_at->timezone,
                    'h5pEdit' => 'http://core/h5p/edit/12/stateless',
                    'maxScore' => 0,
                    'uri' => 'http://ca.cerpus-course.com/random/id/f559e66e-a51a-4722-8529-eb523ee374e3',
                    'uuid' => 'b0e44373-08ba-4f4e-a4f8-ad0344c03c60',
                    'h5pId' => $h5pContent->id,
                    'tags' => '',
                    'shares' => [],
                    'h5pType' => $h5pContent->library->name,
                    'api' => 'http://core/v1/ltitools?uri=http%3A%2F%2Fca.cerpus-course.com%2Frandom%2Fid%2Ff559e66e-a51a-4722-8529-eb523ee374e3',
                    'resourceType' => 'H5P_RESOURCE',
                    'linkCount' => 2,
                ],
                'created' => $h5pContent->created_at->timezone,
                'api' => 'http://core/v1/ltilinks/' . $h5pContent->id,
                'id' => '44444444-4444-4444-4444-444444444444',
                'viewCount' => 0,
                'consumer' => 'http://core/ltics?link=' . $h5pContent->id,
            ])));

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('request')
            ->with('GET', '', [])
            ->willReturn($response);


        $articleCopyright = $this->createPartialMock(ArticleCopyrightController::class, ['getClient']);
        $articleCopyright->method('getClient')->willReturn($client);
        app()->instance(ArticleCopyrightController::class, $articleCopyright);

        $response = $this->get('/article/' . $article->id . '/copyright');
        $response->assertStatus(200);
        $response->assertJson([
            'article' => [
                'license' => 'PRIVATE',
                'attribution' => [
                    'origin' => null,
                    'originators' => [],
                ],
                'assets' => [
                    [
                        'h5p' => null,
                        'h5pLibrary' => [
                            'name' => $h5pContent->library->name,
                            'majorVersion' => $h5pContent->library->major_version,
                            'minorVersion' => $h5pContent->library->minor_version,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
