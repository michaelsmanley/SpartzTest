<?php

use Phinx\Migration\AbstractMigration;

class SpartzTestPopulate extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        # populate cities
        $file = dirname(__FILE__) . "/cities.csv";
        $query = "LOAD DATA INFILE '$file' INTO TABLE city FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r' IGNORE 1 LINES (id, name, state, status, latitude, longitude)";
        $this->execute($query);

        $file = dirname(__FILE__) . "/users.csv";
        $query = "LOAD DATA INFILE '$file' INTO TABLE user FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r' IGNORE 1 LINES (id, first_name, last_name)";
        $this->execute($query);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
         $this->execute('DELETE FROM visit');
         $this->execute('DELETE FROM city');
         $this->execute('DELETE FROM user');
    }
}