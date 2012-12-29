<?php
trait odEntityAssertions
{
  protected function assertResponseClass(stdClass $response, $message = "Wrong response structure")
  {
    $this->assertPropertys($response, [
      'result',
      'errors',
      'status',
      'code',
    ], $message);
  }

  protected function assertProperty($obj, $property, $message = "Property '%s' not found")
  {
    if(!is_object($obj))
      return $this->fail("Expected a object but '".gettype($obj)."' given");

    return $this->assertTrue(
      property_exists($obj, $property),
      sprintf($message, $property)
    );
  }

  protected function assertPropertys(stdClass $obj, array $propertys, $message = "Property '%s' not found")
  {
    return $this->assertProperties($obj, $propertys, $message);
  }

	protected function assertProperties(stdClass $obj, array $properties, $message = "Property '%s' not found")
	{
		foreach ($properties as $property)
		{
			$this->assertProperty($obj, $property, $message);
		}
	}

  public function assertEqualPropertyValues(stdClass $main_object, stdClass $updated_object, $verbose = false)
  {
    foreach ($main_object as $key => $value)
    {
      if(property_exists($updated_object, $key))
        $this->assertEqual($main_object->$key, $value);
      elseif($verbose)
        $this->fail("Property '$key' not found");
    }
  }

  protected function assert404Url($url)
  {
    return $this->assertFalse($this->_isUrlExists($url), "Image exists '{$url}'");
  }

  protected function _isUrlExists($url)
  {
    $images_conf = lmbToolkit::instance()->getConf('images');
    $rel_path = str_replace(lmbToolkit::instance()->getConf('common')['static_host'], '', $url);
    $abs_path = lmb_env_get('APP_DIR').'/'.$images_conf['save_path'].'/'.$rel_path;
    return file_exists($abs_path);
  }

  protected function assertImageUrl($url, $message = "Invalid image url '%s'")
  {
    return $this->assertTrue($this->_isUrlExists($url), sprintf($message, $url));
  }

  protected function assertImageUrls(array $urls)
  {
    foreach ($urls as $url)
    {
      $this->assertImageUrl($url);
    }
  }

  ########### User ###########
  protected function assertJsonUser(stdClass $user)
  {
    $this->assertJsonUserSubentity($user);
    $this->assertPropertys($user, []);
  }

  protected function assertJsonUserListItem(stdClass $user)
  {
    $this->assertJsonUserSubentity($user);

    if(lmbToolkit::instance()->getUser() && lmbToolkit::instance()->getUser()->id != $user->id)
    {
      $this->assertProperty($user, 'following');
    }
  }

  protected function assertJsonUserSubentity(stdClass $user)
  {
    $this->assertPropertys($user, [
      "id",
      "name",
      "sex",
      "image_36",
      "image_72",
      "image_96",
      "image_192",
      "occupation",
      "location",
    ]);

    $this->assertTrue($user->id, "User ID not set");
    $this->assertTrue($user->name, "User name can't be empty");

    $this->assertTrue($user->sex, 'User gender is not set');
    $this->assertTrue(false !== array_search($user->sex, [
      'male',
      'female',
    ]), "Uknown user gender '{$user->sex}'");

    $this->assertImageUrls([
      $user->image_36,
      $user->image_72,
      $user->image_96,
      $user->image_192,
    ]);
  }

  protected function assertJsonUserItems(array $users)
  {
    foreach ($users as $user)
    {
      $this->assertJsonUserListItem($user);
    }
  }

  ########### > FacebookUser ###########
  protected function assertJsonFacebookUserListItem(stdClass $facebook_user, $validate_images = false)
  {
    $this->assertPropertys($facebook_user, [
      "uid",
      "name",
      "image_50",
      "image_150",
      "user",
    ]);

    $this->assertTrue($facebook_user->uid, "User ID not set");
    $this->assertTrue($facebook_user->name, "User name can't be empty");

    if($validate_images)
      $this->assertImageUrls([
        $facebook_user->image_50,
        $facebook_user->image_150,
      ]);
    else
    {
      $this->assertTrue($facebook_user->image_50);
      $this->assertTrue($facebook_user->image_150);
    }

    if(!is_null($facebook_user->user))
      $this->assertJsonUserSubentity($facebook_user->user);
  }

  protected function assertJsonFacebookUserItems(array $facebook_users, $validate_images = false)
  {
    foreach ($facebook_users as $facebook_user)
    {
      $this->assertJsonFacebookUserListItem($facebook_user, $validate_images);
    }
  }

  ########### > Settings ###########
  protected function assertJsonUserSettings(stdClass $settings)
  {
    $this->assertPropertys($settings, [
      "notifications_new_days",
      "notifications_new_comments",
      "notifications_new_replays",
      "notifications_related_activity",
      "notifications_shooting_photos",
      "photos_save_original",
      "photos_save_filtered",
      "social_share_facebook",
      "social_share_twitter",
    ]);

    $this->assertTrue('notifications_new_days');
    $this->assertTrue('notifications_new_comments');
    $this->assertTrue('notifications_new_replays');
    $this->assertTrue('notifications_related_activity');
    $this->assertTrue('notifications_shooting_photos');
    $this->assertTrue('photos_save_original');
    $this->assertTrue('photos_save_filtered');
    $this->assertTrue('social_share_facebook');
    $this->assertTrue('social_share_twitter');
  }

  protected function assertJsonPartnerDeal($json)
  {
	  $this->assertProperties($json, ['id', 'good', 'description', 'image', 'coins_count', 'coins_type', 'shop']);
  }
}
