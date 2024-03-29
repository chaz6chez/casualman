<?php
declare(strict_types=1);

namespace CasualMan\Application\Model;

use Database\AbstractModel;

class BaseModel extends AbstractModel {

    protected function _masterConfig(): array
    {
        $configs = C('database.mysql');
        return isset($configs[$this->_dbName]) ? $configs[$this->_dbName] : [];
    }

    protected function _slaveConfig(): array
    {
        $configs = C('database.mysql');
        return isset($configs[$this->_dbName]['slave']) ? $configs[$this->_dbName]['slave'] : [];
    }

    /**
     * @return static
     */
    public static function instance() : self {
        return Co()->get(get_called_class());
    }

}