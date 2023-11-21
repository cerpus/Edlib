<?php

namespace Tests\Integration\Content;

use App\Article;
use App\ContentLock;
use App\H5PContent;
use App\User;
use Carbon\Carbon;
use Cerpus\VersionClient\VersionData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;
use Tests\Helpers\MockVersioningTrait;

class LockStatusTest extends TestCase
{
    use RefreshDatabase;
    use MockVersioningTrait;

    public function testLockStatus()
    {
        $this->setUpVersioningClient();
        $h5p = H5PContent::factory()->create();
        $lockStatus = ContentLock::factory()->create([
            'content_id' => $h5p->id,
            'updated_at' => Carbon::now()->subSeconds(30)
        ]);
        $user = User::factory()->make();

        $this->withSession(['authId' => $user->auth_id])
            ->get(route('lock.status', $lockStatus->content_id))
            ->assertJson([
                'isLocked' => true,
                'editUrl' => null
            ]);
        $this->assertCount(1, ContentLock::all());
    }

    public function testLockStatusExpired()
    {
        $this->setUpVersioningClient();
        $user = User::factory()->make();
        $originalArticle = Article::factory()->create(
            [
                'id' => '0800e3f5-d7a7-4add-a12a-16df86462837',
                'owner_id' => $user->auth_id,
                'version_id' => '7313f894-4dba-4ea4-9896-9da549e2e88f'
            ]
        );

        Article::factory()->create([
            'id' => '9655b7b5-0f2a-4664-a191-09d874a50cab',
            'version_id' => '7313f894-4dba-4ea4-9896-9da549e2e88f',
            'owner_id' => $user->auth_id,
            'parent_id' => $originalArticle->id
        ]);

        $lockStatus = ContentLock::factory()->create([
            'content_id' => $originalArticle->id,
            'updated_at' => Carbon::now()->subSeconds(91)
        ]);

        $this->withSession(['authId' => $user->auth_id])
            ->get(route('lock.status', $lockStatus->content_id))
            ->assertJson([
                'isLocked' => false,
                'editUrl' => route('article.edit', '9655b7b5-0f2a-4664-a191-09d874a50cab'),
            ]);
        $this->assertCount(1, ContentLock::all());
    }

    public function testLockStatusWithActivePulseButExpired()
    {
        config([
            'feature.content-locking' => true,
            'feature.lock-max-hours' => 20,
        ]);
        $this->setUpVersioningClient();
        $user = User::factory()->make();
        $originalArticle = Article::factory()->create(
            [
                'id' => '0800e3f5-d7a7-4add-a12a-16df86462837',
                'owner_id' => $user->auth_id,
                'version_id' => '7313f894-4dba-4ea4-9896-9da549e2e88f'
            ]
        );

        $lockStatus = ContentLock::factory()->create([
            'content_id' => $originalArticle->id,
            'auth_id' => $user->auth_id,
            'created_at' => Carbon::now()->subSeconds(30),
            'updated_at' => Carbon::now()->subSeconds(30),
        ]);

        $this->assertDatabaseHas('content_locks', [
            'content_id' => $originalArticle->id,
            'auth_id' => $user->auth_id,
        ]);

        $this->withSession(['authId' => $user->auth_id])
            ->post(route('lock.status', $lockStatus->content_id))
            ->assertOk();

        $lockStatus->refresh();
        $this->assertLessThan($lockStatus->updated_at, $lockStatus->created_at);

        $lastUpdated = $lockStatus->updated_at;
        $lockStatus->created_at = Carbon::now()->subDay();
        $lockStatus->save();

        $this->withSession(['authId' => $user->auth_id])
            ->post(route('lock.status', $lockStatus->content_id))
            ->assertOk();

        $lockStatus->refresh();

        $this->assertEquals($lastUpdated, $lockStatus->updated_at);
    }

    public function testYouNeedToBeLoggedIn()
    {
        $this->setUpVersioningClient();
        $user = User::factory()->make();
        $originalArticle = Article::factory()->create(['owner_id' => $user->auth_id]);

        $lockStatus = ContentLock::factory()->create([
            'content_id' => $originalArticle->id,
            'updated_at' => Carbon::now()->subMinutes(40)
        ]);
        $this->get(route('lock.status', $lockStatus->content_id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    private function setUpVersioningClient(): void
    {
        $versionResponse = json_decode('{"data":{"id":"edb86ca1-0975-4a83-b19f-8d5df19d4919","externalSystem":"ContentAuthor","externalReference":"9655b7b5-0f2a-4664-a191-09d874a50cab","externalUrl":"http://content-author.local/article/9655b7b5-0f2a-4664-a191-09d874a50cab","children":[],"createdAt":1478589881606,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"7313f894-4dba-4ea4-9896-9da549e2e88f","externalSystem":"ContentAuthor","externalReference":"0800e3f5-d7a7-4add-a12a-16df86462837","externalUrl":"http://content-author.local/article/0800e3f5-d7a7-4add-a12a-16df86462837","createdAt":1478589812706,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"f86bcda0-743f-4c8d-9e95-c9549f2b2a2a","externalSystem":"ContentAuthor","externalReference":"f41325db-05ed-443d-9578-967d1ebc7854","externalUrl":"http://content-author.local/article/f41325db-05ed-443d-9578-967d1ebc7854","createdAt":1478589654506,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"5aae6917-a50b-4994-851b-8b9b1bf8033f","externalSystem":"ContentAuthor","externalReference":"b37b3fbc-723a-4d2d-a05b-7599a58ce727","externalUrl":"http://content-author.local/article/b37b3fbc-723a-4d2d-a05b-7599a58ce727","createdAt":1478589437523,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"65ae68cc-3d65-4330-9404-5cd839fda41b","externalSystem":"ContentAuthor","externalReference":"437f621e-dfd2-4e1d-a4fe-7b3498d61d3a","externalUrl":"http://content-author.local/article/437f621e-dfd2-4e1d-a4fe-7b3498d61d3a","createdAt":1478588405309,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"dc0c215b-0200-47fe-a94d-ad23ebfcf6ce","externalSystem":"ContentAuthor","externalReference":"46b91325-63b7-403d-851d-cff49e1ce208","externalUrl":"http://content-author.local/article/46b91325-63b7-403d-851d-cff49e1ce208","createdAt":1478588364003,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"2d588117-4e2f-4c58-a522-7a85f1e9fe2f","externalSystem":"ContentAuthor","externalReference":"eb6b3d62-2646-42d7-90c6-f29076fef595","externalUrl":"http://content-author.local/article/eb6b3d62-2646-42d7-90c6-f29076fef595","createdAt":1478588301779,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"e6f12a0e-2181-42b4-b56f-b8a3bcf2e0cb","externalSystem":"ContentAuthor","externalReference":"f79106b2-df61-4a18-ba5b-3edeeddf0665","externalUrl":"http://content-author.local/article/f79106b2-df61-4a18-ba5b-3edeeddf0665","createdAt":1478502495915,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"aaf48281-ea2f-4112-aec5-dbfe6cbb15d9","externalSystem":"ContentAuthor","externalReference":"23a3bd44-747d-4a04-b1ca-1dbe29c0603f","externalUrl":"http://content-author.local/article/23a3bd44-747d-4a04-b1ca-1dbe29c0603f","createdAt":1478502404055,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"b3d41080-1c09-42db-9112-d14ccfed4758","externalSystem":"ContentAuthor","externalReference":"6fa6f983-8837-4c89-a44c-2691c4c2eab5","externalUrl":"http://content-author.local/article/6fa6f983-8837-4c89-a44c-2691c4c2eab5","createdAt":1478262258218,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"8df35559-d03c-410c-b3c3-d977be29a51d","externalSystem":"ContentAuthor","externalReference":"488e383b-bc41-4793-ad44-1de8e21bbb64","externalUrl":"http://content-author.local/article/488e383b-bc41-4793-ad44-1de8e21bbb64","createdAt":1478261566014,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"fac65524-d683-4fce-87a9-32ac27590ded","externalSystem":"ContentAuthor","externalReference":"06731ef9-e7e9-451f-a1a3-511f0eda4573","externalUrl":"http://content-author.local/article/06731ef9-e7e9-451f-a1a3-511f0eda4573","createdAt":1478261455343,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"5933f730-160f-4ecc-a0e9-66091b37d56d","externalSystem":"ContentAuthor","externalReference":"37858341-0b5a-4531-920e-80673bc1e67e","externalUrl":"http://content-author.local/article/37858341-0b5a-4531-920e-80673bc1e67e","createdAt":1478255267412,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"15f06d83-98f2-4806-a5c9-6ff47d7499e8","externalSystem":"ContentAuthor","externalReference":"b1f2e737-1bb9-47b2-94c1-988e5dd660ab","externalUrl":"http://content-author.local/article/b1f2e737-1bb9-47b2-94c1-988e5dd660ab","createdAt":1478252474570,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"622244d8-fa03-4344-ae3f-538c0344b8bc","externalSystem":"ContentAuthor","externalReference":"9d671ae9-87b9-48e2-92ae-ced67fadd45e","externalUrl":"http://content-author.local/article/9d671ae9-87b9-48e2-92ae-ced67fadd45e","createdAt":1478165483705,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"8fcf8294-47e8-4071-9cce-7a184a145b42","externalSystem":"ContentAuthor","externalReference":"ee342b5e-4847-4411-a06b-c013bac4e995","externalUrl":"http://content-author.local/article/ee342b5e-4847-4411-a06b-c013bac4e995","createdAt":1478165386208,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"15c8c457-6293-4581-a10d-b46e81675c78","externalSystem":"ContentAuthor","externalReference":"1ac48d0d-8d08-415b-b6d6-4f0d4fb9d2c8","externalUrl":"http://content-author.local/article/1ac48d0d-8d08-415b-b6d6-4f0d4fb9d2c8","createdAt":1478073565703,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"ffaf8772-0375-4dee-b915-591be6615a6b","externalSystem":"ContentAuthor","externalReference":"45680171-ccb9-45c9-b6e2-3c5ed857eb04","externalUrl":"http://content-author.local/article/45680171-ccb9-45c9-b6e2-3c5ed857eb04","createdAt":1477988675074,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"afdccfa1-d6cb-4e1a-82a1-5c4d6580f382","externalSystem":"ContentAuthor","externalReference":"7ffbab30-8bed-40fd-8059-d1bcf1092e13","externalUrl":"http://content-author.local/article/7ffbab30-8bed-40fd-8059-d1bcf1092e13","createdAt":1477988628640,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"4a940f93-859b-40f1-967f-046869d30d4a","externalSystem":"ContentAuthor","externalReference":"14d45db2-af69-4962-b69f-80c338745ac1","externalUrl":"http://content-author.local/article/14d45db2-af69-4962-b69f-80c338745ac1","createdAt":1477987902990,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"117b521b-5324-4056-831e-8624b0edf06f","externalSystem":"ContentAuthor","externalReference":"5ca53073-a21f-4929-a979-b2c11e890ee1","externalUrl":"http://content-author.local/article/5ca53073-a21f-4929-a979-b2c11e890ee1","createdAt":1477984600460,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"ab4ac2c2-4331-4561-b820-5748cde13985","externalSystem":"ContentAuthor","externalReference":"a07c2ae5-1613-4b8c-a2b6-65e0d774ea2d","externalUrl":"http://content-author.local/article/a07c2ae5-1613-4b8c-a2b6-65e0d774ea2d","createdAt":1477983778237,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"21f412f3-1e31-4abf-b301-87ec84a163a1","externalSystem":"ContentAuthor","externalReference":"645b95ba-e974-4030-9109-474f5979cd79","externalUrl":"http://content-author.local/article/645b95ba-e974-4030-9109-474f5979cd79","createdAt":1477982700139,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"1b455aca-711c-4d17-994c-3d9c39545a0c","externalSystem":"ContentAuthor","externalReference":"82998233-ed0e-4346-bf0d-f30f97386ec8","externalUrl":"http://content-author.local/article/82998233-ed0e-4346-bf0d-f30f97386ec8","createdAt":1477917476398,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"4cfaa4f5-2028-48ef-9b46-db52665783e1","externalSystem":"ContentAuthor","externalReference":"78715a15-701b-41f5-84b9-9c5759c9a476","externalUrl":"http://content-author.local/article/78715a15-701b-41f5-84b9-9c5759c9a476","createdAt":1477916731250,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"4d263690-006a-4c9f-a8c7-9ef529f319cd","externalSystem":"ContentAuthor","externalReference":"3c149366-32ac-4176-9307-1bd037a1954a","externalUrl":"http://content-author.local/article/3c149366-32ac-4176-9307-1bd037a1954a","createdAt":1477916653149,"coreId":null,"versionPurpose":"Save","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":{"id":"92bd2205-af96-4bf9-826a-06e12cb86a1d","externalSystem":"ContentAuthor","externalReference":"1dca1890-998c-400c-990d-deea6f908d3c","externalUrl":"http://content-author.local/article/1dca1890-998c-400c-990d-deea6f908d3c","createdAt":1477916158420,"coreId":null,"versionPurpose":"Create","originReference":null,"originSystem":null,"userId":"90ab88d9-388b-40ec-b1a5-d4f2ff5fc084","parent":null}}}}}}}}}}}}}}}}}}}}}}}}}}},"errors":[],"type":"success","message":null}');
        $versionData = (new VersionData())->populate($versionResponse->data);

        $this->setupVersion(['latest' => $versionData]);
    }
}
