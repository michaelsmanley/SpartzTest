<?php
use Codeception\Util\Stub;

class UserTest extends \Codeception\TestCase\Test
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
    public function testCreateAndDestroyUser()
    {
		$newuser = Model::factory('\\MSMP\\Spartz\\User')
			->create();
			
		$newuser->first_name = 'Michael';
		$newuser->last_name = 'Manley';
		$newuser->save();

		$msm = Model::factory('\\MSMP\\Spartz\\User')
			->where('first_name', 'Michael')
			->where('last_name', 'Manley')
			->find_one();
			
		$this->assertTrue($msm instanceof \MSMP\Spartz\User);

		$msm->delete();

		$msm = Model::factory('\\MSMP\\Spartz\\User')
			->where('first_name', 'Michael')
			->where('last_name', 'Manley')
			->find_one();

		$this->assertEmpty($msm);
    }

}