<?php
lmb_require('limb/web_app/src/controller/lmbController.class.php');
lmb_require('tests/src/odStaticObjectMother.class.php');
lmb_require('tests/src/odObjectMother.class.php');
lmb_require('src/Json.class.php');
lmb_require('src/service/social_provider/odFacebookApiExpiredTokenException.class.php');
lmb_require('src/model/User.class.php');

abstract class BaseJsonController extends lmbController
{
  /**
   * @var odTools
   */
  protected $toolkit;

  function actionExists($action)
  {
    return (bool) $this->_tryFindGuestMethod($action) || (bool) $this->_tryFindUserMethod($action);
  }

  function performAction()
  {
    if($this->is_forwarded)
      return false;

    $guest_method = $this->_tryFindGuestMethod($this->current_action);
    $user_method = $this->_tryFindUserMethod($this->current_action);
    $is_logged = $this->_isLoggedUser();

    if(!$guest_method && !$user_method)
    {
      return $this->forward('not_found', 'display');
    }
    elseif($guest_method && $user_method)
    {
      $method = $is_logged ? $user_method : $guest_method;
    }
    elseif($guest_method && !$user_method)
    {
      $method = $guest_method;
    }
    elseif(!$guest_method && $user_method)
    {
      $method = $user_method;
      if(!$is_logged)
      {
        $this->response->write($this->_answerUnauthorized());
        return $this->_answerUnauthorized();
      }
    }
    return $this->_runMethod($method);
  }

  protected function _runMethod($method)
  {
    try
    {
      $method_response = $this->$method();
    }
    catch(odFacebookApiExpiredTokenException $e)
    {
      $method_response = $this->_answerUnauthorized();
    }

    $this->_passLocalAttributesToView();

    if(is_string($method_response))
      $this->response->write($method_response);
    else
      throw new lmbException("Method '$method' must return a string");

    return $method_response;
  }

  protected function _tryFindGuestMethod($action)
  {
    $method = lmb_camel_case('do_guest_' . $action);
    if(method_exists($this, $method))
      return $method;
    else
      return null;
  }

  protected function _tryFindUserMethod($action)
  {
    $method = lmb_camel_case('do_' . $action);
    if(method_exists($this, $method))
      return $method;

    $method = lmb_camel_case('do_user_' . $action);
    if(method_exists($this, $method))
      return $method;
    else
      return null;
  }

  /**
   * @return User
   */
  protected function _getUser()
  {
    return $this->toolkit->getUser();
  }

  protected function _isLoggedUser()
  {
    return (null != $this->toolkit->getUser()) ? true : false;
  }

  protected function _getFromToLimitations()
  {
    return array(
      (int) $this->request->getFiltered('from', FILTER_SANITIZE_NUMBER_INT),
      (int) $this->request->getFiltered('to', FILTER_SANITIZE_NUMBER_INT),
      (int) $this->request->getFiltered('limit', FILTER_SANITIZE_NUMBER_INT),
    );
  }

  protected function _checkPropertiesInRequest(array $properties)
  {
    foreach($properties as $property)
    {
      if(!$this->request->get($property))
        $this->error_list->addError("Property '$property' not found in request");
    }
    return $this->error_list->getReadable();
  }

  protected function _importSaveAndAnswer($item, array $properties, array $raw_properties = array())
  {
    foreach($properties as $property)
      if($this->request->get($property))
        $item->set($property, $this->request->get($property));

    foreach ($raw_properties as $key => $value)
      $item->set($key, $value);

    $item->validate($this->error_list);
    if($this->error_list->isValid())
    {
      $item->saveSkipValidation();
      $res = $item->exportForApi();

      foreach($res as $key => $property)
        if(is_object($property))
          unset($res[$key]);

      foreach($raw_properties as $key => $property)
        $res->$key = $property;
      return $this->_answerOk($res);
    }
    else
    {
      return $this->_answerWithError($this->error_list->getReadable());
    }
  }

  protected function _answerUnauthorized($message = 'Access allowed only for registered users')
  {
    return $this->_answerWithError($message, null, 401);
  }

  protected function _answerOk($result = null, $status = null, $code = 200)
  {
    if(is_object($result))
    {
      if($result instanceof lmbCollectionInterface)
      {
        $result_array = array();
        foreach($result as $object)
          $result_array[] = $object->exportForApi();
        $result = $result_array;
      }
      elseif($result instanceof BaseModel)
      {
        $result = $result->exportForApi();
      }
    }
    return $this->_answer($result, array(), $status, $code);
  }

  protected function _answerWithError($errors, $status = null, $code = 400)
  {
    if($errors instanceof lmbErrorList)
      $errors = $errors->getReadable();

    if(!is_array($errors))
    {
      if(!$errors)
        throw new lmbException("Error can't be empty");
      $errors = array($errors);
    }
    else
    {
      if(!count($errors))
        throw new lmbException("Error can't be empty");
    }

    return $this->_answer(null, $errors, $status, $code);
  }

  protected function _answerNotFound($message = 'Not Found')
  {
    return $this->_answerWithError([$message], null, 404);
  }

  protected function _answerModelNotFoundById($model_name, $id)
  {
    return $this->_answerNotFound("{$model_name} with id='{$id}' not found");
  }

  protected function _answerNotOwner()
  {
    return $this->_answerWithError("Current user don't have permission to perform this action", null, 401);
  }

  protected function _answerConflict($result = null)
  {
    return $this->_answerOk($result, "Entity already exists", 200);
  }

  protected function _answerNotPost($message = 'Not a POST request')
  {
    return $this->_answerWithError($message, null, 405);
  }

  protected function _answer($result, array $errors, $status, $code)
  {
    if(lmb_app_mode() != 'production')
    {
      $this->response->addHeader('Access-Control-Allow-Origin: *');
      $this->response->addHeader('Access-Control-Allow-Credentials: true');
      $this->response->addHeader('Access-Control-Allow-Headers: Cache-Control, pragma');
    }

    $this->response->setCode($code);
    $this->response->setStatus($status);
    $this->response->setContentType('application/json');

    return json_encode([
        'code'   => $this->response->getCode(),
        'status' => $this->response->getStatus(),
        'result' => $result,
        'errors' => $errors
    ]);
  }
}
