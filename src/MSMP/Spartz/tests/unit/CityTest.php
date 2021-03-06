<?php
use Codeception\Util\Stub;

class CityTest extends \Codeception\TestCase\Test
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

    function testIllinoisCities() {
        $ILCities = Model::factory('\\MSMP\\Spartz\\City')
            ->where('state', 'IL')
            ->find_many();

        $this->assertEquals(524, count($ILCities));
    }
    
    function testChicagoNearby() {
        $Chicago = Model::factory('\\MSMP\\Spartz\\City')
           ->where('name', 'Chicago')
           ->find_one();
           
        $this->assertEquals('Chicago', $Chicago->name);
        
        $nearbyCities = $Chicago->nearby(50);
        
        $this->assertEquals(157, count($nearbyCities));
    }

    function testBogusDistance() {
        $Chicago = Model::factory('\\MSMP\\Spartz\\City')
           ->where('name', 'Chicago')
           ->find_one();
           
	    $nearbyCities = $Chicago->nearby(-17);
	    
	    $this->assertEmpty($nearbyCities);
    }
}
