<?php
namespace MSMP\SpartzTest

class User extends \Model {

    public function visits() {
        return $this->has_many('Visit'); 
    }

    public function cities() {
	    $visits = $this->visits()->find_many();
	    $cities = array();
	    foreach ($visits as $visit)
	    	$cities[$visit->]
	    
    }
}