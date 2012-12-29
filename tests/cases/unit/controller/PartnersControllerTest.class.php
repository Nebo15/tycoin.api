<?php
lmb_require('tests/cases/unit/controller/odControllerTestCase.class.php');
lmb_require('src/model/Transaction.class.php');

class PartnersControllerTest extends odControllerTestCase
{
	protected $controller_class = 'PartnersController';

	function testDeals()
	{
		$response = $this->get('is_logged_in', ['token' => $this->main_user->facebook_access_token]);
		if ($this->assertResponse(200))
			$this->assertFalse($response->result);
	}
}