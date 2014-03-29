<?php
namespace MSMP\Spartz;

class City extends \Model {
    public static $_table = 'city';
    
    public function nearby($radius=0) {
        $radius = (int)$radius;
        if ($radius <= 0)
            return array();
            
        $haversine = <<<HAVERSINE
        (3959 * acos(cos(radians({$this->latitude})) * cos(radians(latitude)) * cos(radians(longitude) -
         radians({$this->longitude})) + sin(radians({$this->latitude})) * sin(radians(latitude))))
HAVERSINE;

        $cities = \Model::factory('\\MSMP\\Spartz\\City')
            ->select('*')
            ->select_expr($haversine, 'distance')
            ->where_not_equal('id', $this->id)
            ->having_lt('distance', $radius)
            ->order_by_asc('distance')
            ->find_many();

         return $cities;
    }
}