<?php
namespace MSMP\Spartz;

class Visit extends \Model {
    public static $_table = 'visit';

    public function user() {
        return $this->belongs_to('\\MSMP\\Spartz\\User');
    }
    
    public function city() {
        return $this->has_one('\\MSMP\\Spartz\\City');
    }
}