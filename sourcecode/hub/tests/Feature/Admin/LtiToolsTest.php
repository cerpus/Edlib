<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Tests\TestCase;

final class LtiToolsTest extends TestCase
{
    public function testCannotShowIndexWhenLoggedOut(): void
    {
        $this->get('/admin/lti-tools')
            ->assertForbidden();
    }

    public function testCannotShowFormWhenLoggedOut(): void
    {
        $this->get('/admin/lti-tools/add')
            ->assertForbidden();
    }

    public function testCannotAddToolsWhenLoggedOut(): void
    {
        $cookie = $this->get('/')->getCookie('XSRF-TOKEN')?->getValue()
            ?? $this->fail('Unable to get CSRF token');

        $this->post('/admin/lti-tools', [
            'name' => 'Not allowed',
            'consumer_key' => 'foo',
            'consumer_secret' => 'bar',
            'creator_launch_url' => 'http://example.com',
            'lti_version' => '1.1',
            'edit_mode' => 'replace',
            '_token' => $cookie,
        ])
            ->assertForbidden();
    }
}
