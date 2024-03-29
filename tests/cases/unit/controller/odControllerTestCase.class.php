<?php
// lmb_require('limb/tests_runner/lib/simpletest/web_tester.php');
lmb_require('lib/DocCommentParser/*.class.php');
lmb_require('lib/limb/net/src/lmbHttpRequest.class.php');
lmb_require('tests/cases/unit/odUnitTestCase.class.php');
lmb_require('limb/net/src/lmbHttpResponse.class.php');
lmb_require('src/model/Transaction.class.php');
lmb_require('tests/src/service/odJobQueueClientForTests.class.php');

Mock::generate('odPostingService', 'PostingServiceMock');
Mock::generate('odFacebook', 'FacebookMock');
Mock::generate('odTwitter', 'TwitterMock');
Mock::generate('FacebookProfile', 'FacebookProfileMock');
Mock::generate('TwitterProfile', 'TwitterProfileMock');

// Cheaty, but sphinxapi.php can't be mocked (errors occured).
class odSearchServiceForMock{ public function find(){} }
Mock::generate('odSearchServiceForMock', 'SearchServiceMock');

/**
 * @property odTools $toolkit
 */
abstract class odControllerTestCase extends odUnitTestCase
{
  /**
   * @var string
   */
  protected $controller_class;
  /**
   * @var stdClass
   */
  protected $last_response;
  protected $last_response_raw;
  protected $cookies = array();

  function setUp()
  {
    if(!$this->controller_class)
      throw new lmbException('You must specify controller class');

    lmb_require('src/controller/'.$this->controller_class.'.class.php');

    parent::setUp();
    $this->toolkit->setFacebook(new FacebookMock, $this->main_user->facebook_access_token);
    $this->toolkit->setTwitter(new TwitterMock(), $this->main_user->twitter_access_token);
    $this->toolkit->setFacebookProfile($this->main_user, new FacebookProfileMock);
    $this->toolkit->setTwitterProfile($this->main_user, new TwitterProfileMock);

    $this->toolkit->setFacebook(new FacebookMock, $this->additional_user->facebook_access_token);
    $this->toolkit->setTwitter(new TwitterMock(), $this->additional_user->twitter_access_token);
    $this->toolkit->setFacebookProfile($this->additional_user, new FacebookProfileMock);
    $this->toolkit->setTwitterProfile($this->additional_user, new TwitterProfileMock);

    $this->toolkit->setJobQueueClient(new odJobQueueClientForTests());

    $this->toolkit->setSearchService('users', new SearchServiceMock);
    $this->toolkit->setSearchService('days', new SearchServiceMock);
  }

  function get($action, $params = array(), $id = null)
  {
    $request = new lmbHttpRequest(null, (array) $params);
    $request->setRequestMethod('GET');
    if($id)
      $request->set('id', $id);
    $result = $this->request($this->controller_class, $action, $request);
    return $result;
  }

  function post($action, $post_params = array(), $id = null)
  {
    $request = new lmbHttpRequest(null, null, (array) $post_params);
    $request->setRequestMethod('POST');
    if($id)
      $request->set('id', $id);
    $result = $this->request($this->controller_class, $action, $request);
    return $result;
  }

  function request($controller_class, $action, lmbHttpRequest $request)
  {
    $this->toolkit->getResponse()->start();
    $this->toolkit->setRequest($request);

    $request->setCookies($this->cookies);
    $controller = new $controller_class;
    $controller->setRequest($request);
    $controller->setCurrentAction($action);
    $this->last_response_raw = $controller->performAction();
    $this->last_response = $this->_decodeResponse($this->last_response_raw);

    $this->assertResponseClass($this->last_response, "Wrong response structure: {$this->last_response_raw}.");

    return clone $this->last_response;
  }

  protected function _decodeResponse($raw_response)
  {
    $decoded_body = json_decode($raw_response);
    if ($decoded_body === null) {
      throw new lmbException("Can't parse response", array(
          'raw' => $raw_response
      ));
    }
    return $decoded_body;
  }

  function assertResponse($responses)
  {
    $responses = (is_array($responses) ? $responses : array($responses));
    $code = $this->last_response->code;
    $message = sprintf('%s', "Expecting response in [" .
      implode(", ", $responses) . "] got [$code] in response:".($this->last_response_raw));
    return $this->assertTrue(in_array($code, $responses), $message);
  }
}
