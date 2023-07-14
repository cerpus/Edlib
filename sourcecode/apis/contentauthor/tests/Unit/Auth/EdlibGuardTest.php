<?php

namespace Tests\Unit\Auth;

use App\Auth\Guards\EdlibGuard;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\TestCase;

class EdlibGuardTest extends TestCase
{
    public function testGuestReturnsTrueWhenUserIsNotSet()
    {
        $request = Request::create('/', 'GET');
        $guard = new EdlibGuard($request);

        $result = $guard->guest();

        $this->assertTrue($result);
    }

    public function testGuestReturnsFalseWhenUserIsSet()
    {
        $request = Request::create('/', 'GET');
        $user = new GenericUser(['id' => 1]);
        Session::put('user', $user);
        $guard = new EdlibGuard($request);

        $result = $guard->guest();

        $this->assertFalse($result);
    }

    public function testIdReturnsUserIdWhenUserIsSet()
    {
        $request = Request::create('/', 'GET');
        $user = new GenericUser(['id' => 1]);
        Session::put('user', $user);
        $guard = new EdlibGuard($request);

        $result = $guard->id();

        $this->assertEquals(1, $result);
    }

    public function testSetUserSetsUserAndStoresInSession()
    {
        $request = Request::create('/', 'GET');
        $user = new GenericUser(['id' => 1]);
        $guard = new EdlibGuard($request);

        $guard->setUser($user);
        $sessionUser = Session::get('user');

        $this->assertSame($user, $guard->user());
        $this->assertSame($user, $sessionUser);
    }

    public function testValidateThrowsException()
    {
        $request = Request::create('/', 'GET');
        $guard = new EdlibGuard($request);

        $this->expectException(\BadMethodCallException::class);

        $guard->validate();
    }

    public function testAuthenticateReturnsGenericUser()
    {
        $request = Request::create('/', 'GET');
        $user = new GenericUser(['id' => 1]);
        Session::put('user', $user);
        $guard = new EdlibGuard($request);

        $result = $guard->authenticate();

        $this->assertInstanceOf(GenericUser::class, $result);
        $this->assertSame($user, $result);
    }

    public function testHasUserReturnsTrueWhenUserIsSet()
    {
        $request = Request::create('/', 'GET');
        $user = new GenericUser(['id' => 1]);
        Session::put('user', $user);
        $guard = new EdlibGuard($request);

        $result = $guard->hasUser();

        $this->assertTrue($result);
    }

    public function testAttemptReturnsFalse()
    {
        $request = Request::create('/', 'GET');
        $guard = new EdlibGuard($request);

        $result = $guard->attempt();

        $this->assertFalse($result);
    }

    public function testOnceReturnsFalse()
    {
        $request = Request::create('/', 'GET');
        $guard = new EdlibGuard($request);

        $result = $guard->once();

        $this->assertFalse($result);
    }

    public function testLoginSetsUserAndStoresInSession()
    {
        $request = Request::create('/', 'GET');
        $user = new GenericUser(['id' => 1]);
        $guard = new EdlibGuard($request);

        $guard->login($user);
        $sessionUser = Session::get('user');

        $this->assertSame($user, $guard->user());
        $this->assertSame($user, $sessionUser);
    }

    public function testLoginUsingIdReturnsFalse()
    {
        $request = Request::create('/', 'GET');
        $guard = new EdlibGuard($request);

        $result = $guard->loginUsingId(1);

        $this->assertFalse($result);
    }

    public function testOnceUsingIdReturnsFalse()
    {
        $request = Request::create('/', 'GET');
        $guard = new EdlibGuard($request);

        $result = $guard->onceUsingId(1);

        $this->assertFalse($result);
    }

    public function testViaRememberReturnsFalse()
    {
        $request = Request::create('/', 'GET');
        $guard = new EdlibGuard($request);

        $result = $guard->viaRemember();

        $this->assertFalse($result);
    }

    public function testLogoutClearsUser()
    {
        $request = Request::create('/', 'GET');
        $user = new GenericUser(['id' => 1]);
        Session::put('user', $user);
        $guard = new EdlibGuard($request);

        $guard->login($user);
        $guard->logout();
        $sessionUser = Session::get('user');

        $this->assertNull($guard->user());
        $this->assertNull($sessionUser);
    }
}
