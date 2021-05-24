<?php
declare(strict_types=1);

namespace CasualMan\Demo\Service;

use CasualMan\Demo\Model\DemoModel;
use Kernel\Utils\Response;
use Kernel\Utils\Service;

class DemoService extends Service {
    public function Demo() : Response{
        try {
            /** @var DemoModel $demoM */
            $demoM = Co()->get(DemoModel::class);
            $res = $demoM->get(1);
        }catch (\Throwable $throwable){
            return $this->response()->error($throwable->getMessage(), $throwable->getCode());
        }
        return $this->response()->success($res);
    }
}