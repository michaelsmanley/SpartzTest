<?php
namespace MSMP\Spartz;

class User extends \Model {
    public static $_table = 'user';

    public function visits() {
        return $this->has_many('\\MSMP\\Spartz\\Visit'); 
    }

    public function full_name() {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function cities() {
        $visits = $this->visits()->find_many();
        $cids = array();
        foreach ($visits as $visit)
            $cids[] = $visit->city_id;
        $cities = \Model::factory('\\MSMP\\Spartz\\City')
            ->where_in('id', $cids)
            ->find_many();
        return $cities;
    }
}