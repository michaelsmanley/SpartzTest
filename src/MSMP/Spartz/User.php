<?php
namespace MSMP\Spartz;

class User extends \Model {
    public static $_table = 'user';

    public function visits() {
        return $this->has_many('Visit'); 
    }

    public function cities() {
        $visits = $this->visits()->find_many();
        $cids = array();
        foreach ($visits as $visit)
            $cids[] = $visit->id;
        $cities = Model::factory('City')
            ->where_in('id', $cids)
            ->find_many();
        return $cities;
    }
}