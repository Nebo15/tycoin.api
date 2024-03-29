<?php
lmb_require(taskman_prop('PROJECT_DIR').'setup.php');

lmb_require('src/model/User.class.php');
lmb_require('src/model/DeviceToken.class.php');
lmb_require('src/model/InternalShopDeal.class.php');
lmb_require('src/service/MoneyService.class.php');

function task_restore_free_coin()
{
	$service = new MoneyService();
	foreach(User::find() as $user)
	{
		taskman_msg('User #'.$user->id.'...');
		$transaction = $service->tryRestore($user, InternalShopDeal::freeCoinDeal());
		if(!$transaction)
			taskman_msg("SKIPPED".PHP_EOL);
		else
			taskman_msg("transaction #".$transaction->id.PHP_EOL);
	}
}

function task_od_apns_feedback()
{
  $apns = lmbToolkit::instance()->getApns();

  od_apns_connect($apns, 10);

  $tokens = $apns->feedback();
  while(list($token, $time) = each($tokens))
  {
    echo $time . "\t" . $token . PHP_EOL;
  }
  $apns->close();
}

function task_od_apns_push()
{
  $apns = lmbToolkit::instance()->getApns();

  taskman_msg("Try to connect to APNS...");
  od_apns_connect($apns, 10);
  taskman_msg("DONE".PHP_EOL);

  foreach(DeviceNotification::findNotSended() as $notification)
  {
    $notification_age_in_secs = time() - $notification->ctime;
    if(!$notification->device_token_id || 24*60*60 < $notification_age_in_secs)
    {
      $notification->destroy();
      continue;
    }

    $message = new Zend_Mobile_Push_Message_Apns();
    $message->setAlert($notification->text);
    $message->setBadge($notification->icon ?: 1);
    $message->setSound($notification->sound ?: 'default');
    $message->setId($notification->id);
    $message->setToken($notification->getDeviceToken()->token);

    try
    {
      $apns->send($message);
      $notification->is_sended = 1;
      $notification->save();
    }
    catch (Zend_Mobile_Push_Exception_InvalidToken $e)
    {
      $notification->getDeviceToken()->destroy();
      $notification->destroy();
    }
    catch (lmbException $e)
    {
      lmbToolkit::instance()->getLog()->logException($e);
      continue;
    }

    $apns->close();
  }
}

function od_apns_connect($apns, $attempts)
{
  taskman_msg($attempts." ");
  if(!$attempts)
  {
    taskman_sysmsg("Can't connect");
    exit(1);
  }

  try
  {
    $apns->connect(Zend_Mobile_Push_Apns::SERVER_SANDBOX_URI);
  }
  catch (Zend_Mobile_Push_Exception_ServerUnavailable $e)
  {
    sleep(10);
    od_apns_connect($apns, $attempts-1);
    exit(1);
  }
  catch (Zend_Mobile_Push_Exception $e)
  {
    taskman_sysmsg('APNS Connection Error:' . $e->getMessage());
    exit(1);
  }
}

function task_od_job_worker()
{
  lmb_require('src/service/odAsyncJobs.class.php');

  $worker= new GearmanWorker();
  $worker->addServer();
  foreach(get_class_methods('odAsyncJobs') as $function)
  {
    $worker->addFunction($function, array("odAsyncJobs", $function));
  }
  while($worker->work());
}

function task_od_bundle()
{
  set_time_limit(0);

  lmb_require('limb/bundle/src/lmbBundler.class.php');

  $bundler = new lmbBundler(get_include_path(), true);

  $files = json_decode(file_get_contents(lmb_env_get('HOST_URL').'main_page/bundle_files'))->result;

  foreach($files as $file)
    $bundler->add($file);

  $result = $bundler->makeBundle(true);

  $lines_arr = preg_split('/\n|\r/', $result);
  $num_newlines = count($lines_arr);

  $setup_file_content = file_get_contents(taskman_prop('PROJECT_DIR').'setup.php');

  $result = str_replace("require_once('limb/core/common.inc.php');", $result, $setup_file_content);

  file_put_contents(taskman_prop('PROJECT_DIR').'/bundle.php', $result);

  echo "Bundled $num_newlines lines".PHP_EOL;

//  var_dump($bundler->getIncludes());
}
