<?php
declare(strict_types=1);

namespace CasualMan\Application\Service;

use Kernel\Utils\Service;

class BaseService extends Service
{

    protected function _initConfig()
    {
        $this->setConfigs(C('service.' . get_called_class(), []));
        parent::_initConfig();
    }
}