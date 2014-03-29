<?php
namespace MSMP\Spartz;

class Visit extends \Model {
    public static $_table = 'visit';

    public function user() {
        return $this->belongs_to('\\MSMP\\Spartz\\User');
    }
    
    public function setUser(\MSMP\Spartz\User $u) {
	    $this->user_id = $u->id;
    }
    
    public function city() {
        return \Model::factory('\\MSMP\\Spartz\\City')
        	->where('id', $this->city_id)
        	->find_one();
    }
    
    public function setCity(\MSMP\Spartz\City $c) {
	    $this->city_id = $c->id;
    }
}