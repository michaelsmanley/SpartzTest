<?php
use Codeception\Util\Stub;

class VisitTest extends \Codeception\TestCase\Test
{
   /**
    * @var \CodeGuy
    */
    protected $codeGuy;

    protected function _before()
    {
      ORM::configure('mysql:host=localhost;dbname=spartztest');
      ORM::configure('username', 'spartztest');
      ORM::configure('password', 'spartztest');
    }

    protected function _after()
    {
    }

    // tests
    public function testCreateAndDestroyVisit()
    {
		$first_user_id = Model::factory('\\MSMP\\Spartz\\User')
			->min('id');
			
		$first_city_id = Model::factory('\\MSMP\\Spartz\\City')
			->min('id');
			
		$user = Model::factory('\\MSMP\\Spartz\\User')
			->where('id', $first_user_id)
			->find_one();
			
		$city = Model::factory('\\MSMP\\Spartz\\City')
			->where('id', $first_city_id)
			->find_one();
			
		$visit = Model::factory('\\MSMP\\Spartz\\Visit')
			->create();
			
		$visit->setUser($user);
		$visit->setCity($city);
		$visit->save();
		
		$visits = $user->visits()->find_many();
		$this->assertEquals(1, count($visits));
		$this->assertEquals($user->id, $visits[0]->user()->find_one()->id);
		$this->assertEquals($city->id, $visits[0]->city()->id);
		
		$cities = $user->cities();
		$this->assertEquals(1, count($cities));
		$this->assertEquals($city->id, $cities[0]->id);
		
		$visit->delete();
		$visits = $user->visits()->find_many();
		$this->assertEmpty($visits);
    }

}