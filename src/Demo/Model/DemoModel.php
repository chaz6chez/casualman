<?php
declare(strict_types=1);

namespace CasualMan\Demo\Model;

class DemoModel extends BaseModel {
    protected $_dbName = '3y_clearing';
    protected $_table  = 'user_assets';

    public function get(int $id){
        return $this->dbName()->table($this->_table)->where([
            'id' => $id
        ])->find();
    }
}