<?php
lmb_require('tests/cases/unit/controller/odControllerTestCase.class.php');
lmb_require('src/model/Transaction.class.php');

class PartnersControllerTest extends odControllerTestCase
{
	use odEntityAssertions;

	protected $controller_class = 'PartnersController';

	function testDeals()
	{
		$response = $this->get('deals', ['token' => $this->main_user->facebook_access_token]);
		if ($this->assertResponse(200))
		{
			$this->assertEqual(2, count($response->result));
			$this->assertJsonPartnerDeal($response->result[0]);
			$this->assertJsonPartnerDeal($response->result[1]);
		}
	}
}