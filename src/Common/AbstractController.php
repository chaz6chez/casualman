<?php
declare(strict_types=1);

namespace CasualMan\Common;

use Workerman\Connection\ConnectionInterface;

abstract class AbstractController
{

    /** @var ConnectionInterface|null */
    protected $_connection;

    public function setConnection(ConnectionInterface $connection){
        $this->_connection = $connection;
    }

    public function getConnection() : ?ConnectionInterface
    {
        return $this->_connection;
    }

    public static function analyzeError($msg){
        $error = explode('|',$msg, 2);
        if(count($error) > 1){
            return $error;
        }
        return false;
    }

    abstract public function init();
    abstract public function reject();
    abstract public function success($data, $msg = 'success');
    abstract public function error($msg, $code = 0, $data = []);
}