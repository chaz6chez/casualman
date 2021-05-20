<?php
declare(strict_types=1);

namespace CasualMan\Clearing\Model;

class DemoModel extends BaseModel {
    protected $_dbName = '';
    protected $_table  = '';

    public function get(int $id){
        return $this->dbName()->where([
            'id' => $id
        ])->find();
    }
}