<?php
declare(strict_types=1);

namespace CasualMan\Demo\Service;

use CasualMan\Demo\Model\DemoModel;
use Kernel\Utils\Response;
use Kernel\Utils\Service;

class DemoService extends Service {
    public function Demo() : Response{
        /** @var DemoModel $demoM */
        try {
            $demoM = Co()->get(DemoModel::class);
            $res = $demoM->get(1);
            dump($demoM);
        }catch (\Throwable $throwable){
            dump($throwable);
        }
        return $this->response()->success(rand(100,999));
    }
}