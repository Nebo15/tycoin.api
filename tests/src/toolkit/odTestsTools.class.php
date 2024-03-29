<?php
lmb_require('limb/dbal/src/lmbSimpleDb.class.php');
lmb_require('tests/src/odObjectMother.class.php');
lmb_require('tests/src/odPostmanWriter.class.php');
lmb_require('tests/src/odApiToMarkdownWriter.class.php');
lmb_require('tests/src/odModelToEntitiesWriter.class.php');

class odTestsTools extends lmbAbstractTools
{
  /**
   * @var odPostmanWriter
   */
  protected $postman_writer;

  /**
   * @var odApiToMarkDownWriter
   */
  protected $api_to_markdown_writer;

  /**
   * @var odModelToEntitiesWriter
   */
  protected $model_to_entities_writer;

  /**
   * @return odPostmanWriter
   */
  function getPostmanWriter()
  {
    if(!$this->postman_writer)
      $this->postman_writer = new odPostmanWriter();
    return $this->postman_writer;
  }

  /**
   * @return odApiToMarkDownWriter
   */
  function getApiToMarkdownWriter()
  {
    if(!$this->api_to_markdown_writer)
      $this->api_to_markdown_writer = new odApiToMarkdownWriter(lmb_env_get('APP_DIR').'/www/api_doc/examples.markdown');
    return $this->api_to_markdown_writer;
  }

  /**
   * @return odModelToEntitiesWriter
   */
  function getModelToEntitiesWriter() {
    if(!$this->model_to_entities_writer)
      $this->model_to_entities_writer = new odModelToEntitiesWriter(lmb_env_get('APP_DIR').'/www/api_doc/entities.markdown');
    return $this->model_to_entities_writer;
  }

  /**
   * @return lmbSimpleDb
   */
  function getSimpleDb() {
    static $db;
    if(!$db)
      $db = new lmbSimpleDb(lmbToolkit::instance()->getDefaultDbConnection());
    return $db;
  }

  function getTestsUsers($all = false)
  {
    $facebook_test_users = lmbToolkit::instance()->loadTestsUsersInfo();
    if($all)
      $users_infos = $facebook_test_users;
    else
      $users_infos = array(array_pop($facebook_test_users), array_pop($facebook_test_users));

    if(!count($users_infos))
    {
      echo "Can't load users from fb";
      exit(1);
    }

    $users = array();
    foreach($users_infos as $key => $user_info)
    {
      $user_info = (object) $user_info;
      $user = new User();
      $user->facebook_uid = $user_info->id;
      $user->facebook_access_token = $user_info->access_token;

      $info = (new FacebookProfile($user))->getInfo();
      $user->email = $info['email'];
      $user->name = $info['name'];
      $user->sex = $info['sex'];
      $user->timezone = $info['timezone'];
      $user->facebook_profile_utime = $info['facebook_profile_utime'];
      $user->occupation = $info['occupation'];
      $user->location = $info['current_location'];
      $user->birthday = $info['birthday'];

      $twitter_credentials = (new odObjectMother())->twitter_credentials()[$key % 2];
      $user->twitter_uid = $twitter_credentials['uid'];
      $user->twitter_access_token = $twitter_credentials['access_token'];
      $user->twitter_access_token_secret = $twitter_credentials['access_token_secret'];
      $users[] = $user;
    }
    return $users;
  }

  function truncateDb()
  {
    $connection = lmbToolkit::instance()->getDefaultDbConnection();

    foreach($connection->getDatabaseInfo()->getTableList() as $table) {
      if($table != 'schema_info')
      {
        $table = $connection->quoteIdentifier($table);
        $connection->newStatement(sprintf("DELETE FROM %s", $table))
          ->execute();
      }
    }
    return $this;
  }

  function makeUniqueTablesIdsOffset($offset)
  {
    $connection = lmbToolkit::instance()->getDefaultDbConnection();

    foreach($connection->getDatabaseInfo()->getTableList() as $table) {
      if($table != 'schema_info')
      {
        $table = $connection->quoteIdentifier($table);
        $connection->newStatement(sprintf("ALTER TABLE %s AUTO_INCREMENT = %s", $table, $offset))
          ->execute();
      }
    }
    return $this;
  }

  function checkServer($uri_string)
  {
    $uri = new lmbUri($uri_string);
    $fp = @fsockopen($uri->getHost(), $uri->getPort() ?: 80, $errno, $errstr, 30);
    if (!$fp) {
      echo("Can't connect to server '$uri_string': $errstr ($errno)\n");
      exit(1);
    } else {
      fclose($fp);
    }
  }

  /**
   * @return lmbAuditDbConnection
   */
  function wrapDefaultDbConnectionWithProfiler()
  {
    $connection = lmbToolkit::instance()->getDefaultDbConnection();
    if('lmbAuditDbConnection' != get_class($connection))
    {
      $connection = new lmbAuditDbConnection($connection);
      lmbToolkit::instance()->setDefaultDbConnection($connection);
    }
    return $connection;
  }
}
