<?php

use Phinx\Migration\AbstractMigration;

class SpartzTestBuild extends AbstractMigration
{
    public function change()
    {
        if (! $this->hasTable('city')) {
            $city = $this->table('city');
            $city->addColumn('name',      'string', array('null'=>FALSE))
                 ->addColumn('state',     'string', array('null'=>FALSE))
                 ->addColumn('status',    'string', array('null'=>FALSE))
                 ->addColumn('latitude',  'float',  array('null'=>FALSE))
                 ->addColumn('longitude', 'float',  array('null'=>FALSE))
                 ->addIndex(array('id'))
                 ->create();
        }

        if (! $this->hasTable('user')) {
            $user = $this->table('user');
            $user->addColumn('first_name', 'string', array('null'=>FALSE))
                 ->addColumn('last_name',  'string', array('null'=>FALSE))
                 ->addIndex(array('id'))
                 ->create();
        }
             
        if (! $this->hasTable('visit')) {
            $visit = $this->table('visit', array('id'=>FALSE));
            $visit->addColumn('user_id', 'integer', array('null'=>FALSE))
                  ->addColumn('city_id', 'integer', array('null'=>FALSE))
                  ->create();
                  
            $visit->addForeignKey('user_id', 'user', 'id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                  ->addForeignKey('city_id', 'city', 'id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                  ->addIndex(array('user_id'))
                  ->addIndex(array('city_id'))
                  ->addIndex(array('user_id', 'city_id'), array('unique'=>TRUE))
                  ->save();
        }
    }
    
    /**
     * Migrate Up.
     */
    public function up()
    {
    
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}