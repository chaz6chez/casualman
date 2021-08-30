<?php
declare(strict_types=1);

namespace CasualMan\Demo\Model;

class DemoModel extends BaseModel {
    protected $_dbName = 'demo';
    protected $_table  = 'demo';

    public function get(int $id){
        return $this->dbName()->table($this->_table)->where([
            'id' => $id
        ])->find();
    }
}