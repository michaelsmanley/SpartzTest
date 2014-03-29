<?php
namespace MSMP\SpartzTest

class Visit extends \Model {

    public function user() {
        return $this->belongs_to('User');
    }
    
    public function city() {
	    return $this->has_one('City');
    }
}