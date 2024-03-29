<?php
lmb_require('tests/cases/unit/controller/odControllerTestCase.class.php');
lmb_require('src/model/Transaction.class.php');

class AuthControllerTest extends odControllerTestCase
{
  protected $controller_class = 'AuthController';

  /**
   * @api description Returns user authentication status.
   * @api result bool - TRUE if user is logged id, else - FALSE
   */
  function testIsLoggedIn()
  {
    $response = $this->get('is_logged_in', ['token' => $this->main_user->facebook_access_token]);
    if($this->assertResponse(200))
      $this->assertFalse($response->result);
  }

	function testMobileFacebookLogin()
	{
		$response = $this->get('mobile_facebook_login');
		var_dump($response->result);
	}

  /**
   * @api description Authorizes and returns User.
   * @api input param string[118] token Facebook access token
   */
  function testLogin()
  {
    $this->main_user->save();
    $this->additional_user->save();

    $this->toolkit->getFacebook($this->additional_user)->setReturnValue('getUid', $this->additional_user->facebook_uid);

    $response = $this->post('login', array(
      'token'        => $access_token = $this->additional_user->facebook_access_token,
      'device_token' => $device_token = $this->generator->string(64)
    ));

    if($this->assertResponse(200))
    {
      $user = $response->result;
      $this->assertJsonUser($user);

      $loaded_user = User::findById($user->id);
      $this->assertEqual($loaded_user->facebook_access_token, $access_token);

      $tokens = $loaded_user->getDeviceTokens();
      $this->assertEqual(1, count($tokens));
      $this->assertEqual($device_token, $tokens->at(0)->token);

      $cookies = $this->toolkit->getResponse()->getCookies();
      $this->assertTrue(array_key_exists('token', $cookies));
      $this->assertEqual($cookies['token']['value'], $access_token);
      $this->assertTrue($cookies['token']['expire'] > time());
    }
  }

  function testLogin_WithoutDeviceToken()
  {
    $this->additional_user->save();

    $this->toolkit->getFacebook($this->additional_user)->setReturnValue('getUid', $this->additional_user->facebook_uid);

    $response = $this->post('login', array(
      'token' => $this->additional_user->facebook_access_token,
    ));

    if($this->assertResponse(200))
    {
      $user = $response->result;
      $this->assertJsonUser($user);

      $loaded_user = User::findById($user->id);
      $this->assertEqual($loaded_user->facebook_access_token, $this->additional_user->facebook_access_token);

      $tokens = $loaded_user->getDeviceTokens();
      $this->assertEqual(0, count($tokens));
    }
  }

  function testLogin_AndSetCookie()
  {
    $this->main_user->save();
    $this->toolkit->getFacebook($this->main_user)->setReturnValue('getUid', $this->main_user->facebook_uid);

    $this->post('login', [
      'token'        => $this->main_user->facebook_access_token,
      'device_token' => $this->generator->string(64)
    ]);

    if($this->assertResponse(200))
    {
      $response = $this->get('is_logged_in', [
        'token' => $this->main_user->facebook_access_token
      ]);

      if($this->assertResponse(200))
        $this->assertTrue($response->result);
    }
  }

  function testLogin_Session_ByGetParam()
  {
    $this->main_user->save();
    $this->toolkit->getFacebook($this->main_user)->setReturnValue('getUid', $this->main_user->facebook_uid);

    $this->post('login', [
      'token'        => $this->main_user->facebook_access_token,
      'device_token' => $this->generator->string(64)
    ]);

    if($this->assertResponse(200))
    {
      $response = $this->get('is_logged_in', [
        'token' => $this->main_user->facebook_access_token
      ]);

      if($this->assertResponse(200))
        $this->assertTrue($response->result);
    }
  }

  function testLogin_Session_ByPostParam()
  {
    $this->main_user->save();
    $this->toolkit->getFacebook($this->main_user)->setReturnValue('getUid', $this->main_user->facebook_uid);

    $this->post('login', [
      'token'        => $this->main_user->facebook_access_token,
      'device_token' => $this->generator->string(64)
    ]);

    if($this->assertResponse(200))
    {
      $response = $this->get('is_logged_in', [
        'token' => $this->main_user->facebook_access_token
      ]);

      if($this->assertResponse(200))
        $this->assertTrue($response->result);
    }
  }

  function testLogin_FirstCallCreateNewUser()
  {
    $this->main_user->destroy();
    $this->additional_user->destroy();

    $new_user = $this->generator->user();

    $this->toolkit->setFacebook(new FacebookMock, $new_user->facebook_access_token);
    $this->toolkit->getFacebook($new_user)->setReturnValue('getUid', $new_user->facebook_uid);

    $info = $this->generator->facebookInfo($new_user->facebook_uid);

    $this->toolkit->setFacebookProfile($new_user, new FacebookProfileMock);
    $profile = $this->toolkit->getFacebookProfile($new_user);
    $profile->setReturnValue('getInfo', $info);
    $profile->setReturnValue('getRegisteredFriends', []);

    $this->post('login', [
      'token'        => $new_user->facebook_access_token,
      'device_token' => $this->generator->string(64)
    ]);
    if($this->assertResponse(200))
    {
      $users = User::find();
      $this->assertEqual(1, count($users));
    }

    $this->post('login', [
      'token'        => $new_user->facebook_access_token,
      'device_token' => $this->generator->string(64)
    ]);
    if($this->assertResponse(200))
    {
      $users = User::find();
      $this->assertEqual(1, count($users));
      $user = $this->toolkit->getExportHelper()->exportUser($users->at(0));
      $this->assertJsonUser($user);
    }
  }

  /**
   * @api
   */
  function testLogin_WrongAccessToken()
  {
    $this->cookies = [];

    $response = $this->post('login', [
      'token'        => 'wrong_access_token',
      'device_token' => $this->generator->string(64)
    ]);

    if($this->assertResponse(403))
    {
      $this->assertTrue(is_null($response->result));

      $errors = $response->errors;
      if($this->assertEqual(1, count($errors)))
        $this->assertEqual('Token expired', $errors[0]);
    }
  }

  function testLogin_AccessTokenNotGiven()
  {
    $this->cookies = [];

    $response = $this->post('login', [
      'device_token' => $this->generator->string(64)
    ]);

    if($this->assertResponse(412))
    {
      $this->assertTrue(is_null($response->result));

      $errors = $response->errors;
      if($this->assertEqual(1, count($errors)))
        $this->assertEqual('Token not given', $errors[0]);
    }
  }

  function testLogin_FromSeveralDevices()
  {
    $this->main_user->save();
    $this->toolkit->getFacebook($this->main_user)->setReturnValue('getUid', $this->main_user->facebook_uid);

    $this->post('login', array(
      'token'        => $this->main_user->facebook_access_token,
      'device_token' => $device_token_1 = $this->generator->string(64)
    ));

    $this->post('login', array(
      'token'        => $this->main_user->facebook_access_token,
      'device_token' => $device_token_2 = $this->generator->string(64)
    ));

    $tokens = $this->main_user->getDeviceTokens();
    if($this->assertEqual(2, count($tokens)))
    {
      $this->assertEqual($device_token_1, $tokens->at(0)->token);
      $this->assertEqual($device_token_2, $tokens->at(1)->token);
    }
  }

  function testLogin_DeviceOwnerChanged()
  {
    $this->main_user->save();
    $this->toolkit->getFacebook($this->main_user)->setReturnValue('getUid', $this->main_user->facebook_uid);

    $this->additional_user->save();
    $this->toolkit->getFacebook($this->additional_user)->setReturnValue('getUid', $this->additional_user->facebook_uid);

    $device_token = $this->generator->string(64);

    $this->post('login', array(
      'token'        => $this->main_user->facebook_access_token,
      'device_token' => $device_token
    ));

    $this->post('login', array(
      'token'        => $this->additional_user->facebook_access_token,
      'device_token' => $device_token
    ));

    $tokens = $this->main_user->getDeviceTokens();
    $this->assertEqual(0, count($tokens));

    $tokens = $this->additional_user->getDeviceTokens();
    if($this->assertEqual(1, count($tokens)))
      $this->assertEqual($device_token, $tokens->at(0)->token);
  }

  function testLogin_FirstCallCreateNewUserWithDefaultAvatar()
  {
    $this->main_user->destroy();
    $this->toolkit->getFacebook($this->additional_user)->setReturnValue('getUid', $this->additional_user->facebook_uid);

    $profile = $this->toolkit->getFacebookProfile($this->additional_user);
    $facebook_info = $this->generator->facebookInfo($this->additional_user->facebook_uid);
    $facebook_info['pic'] = 'http://fb.com/default_image.gif';
    $profile->setReturnValue('getInfo', $facebook_info);
    $profile->setReturnValue('getRegisteredFriends', []);

    $this->post('login', [
      'token'        => $this->additional_user->facebook_access_token,
      'device_token' => $this->generator->string(64)
    ]);

    $users = User::find();
    if($this->assertEqual(1, count($users)))
      $this->assertJsonUser($this->toolkit->getExportHelper()->exportUser($users->at(0)));
  }

  function testLogin_TokenLengthGreaterThan128()
  {
    $this->get('is_logged_in', [
      'token' => $this->generator->string(200)
    ]);
    $this->assertResponse(200);
  }

  function testLogout()
  {
    lmbToolkit::instance()->setUser($this->main_user);

    $this->post('logout', [
      'token'        => $this->main_user->facebook_access_token,
      'device_token' => $this->generator->string()
    ]);

    $cookies = $this->toolkit->getResponse()->getCookies();
    if($this->assertTrue(array_key_exists('token', $cookies)))
    {
      $this->assertEqual($cookies['token']['value'], '');
      $this->assertEqual($cookies['token']['expire'], 1);
    }

    if($this->assertResponse(200))
    {
      $response = $this->get('is_logged_in', [
        'token' => $this->main_user->facebook_access_token
      ]);

      if($this->assertResponse(200))
        $this->assertFalse($response->result);
    }
  }

  function testLogout_RemoveDeviceToken()
  {
    $device_token = $this->generator->deviceToken($this->main_user);

    lmbToolkit::instance()->setUser($this->main_user);

    $this->post('logout', [
      'token'        => $this->main_user->facebook_access_token,
      'device_token' => $device_token->token
    ]);

    if($this->assertResponse(200))
      $this->assertEqual(0, count($this->main_user->getDeviceTokens()));
  }
}
