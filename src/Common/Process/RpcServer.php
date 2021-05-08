<?php
declare(strict_types=1);

namespace CasualMan\Common\Process;

use Kernel\AbstractProcess;
use Kernel\Protocols\ListenerInterface;

class RpcServer extends AbstractProcess implements ListenerInterface{

    public function onStart(): void
    {
        // TODO: Implement onStart() method.
    }

    public function onReload(): void
    {
        // TODO: Implement onReload() method.
    }

    public function onStop(): void
    {
        // TODO: Implement onStop() method.
    }

    public function onBufferDrain()
    {
        // TODO: Implement onBufferDrain() method.
    }

    public function onBufferFull()
    {
        // TODO: Implement onBufferFull() method.
    }

    public function onClose()
    {
        // TODO: Implement onClose() method.
    }

    public function onConnect()
    {
        // TODO: Implement onConnect() method.
    }

    public function onError()
    {
        // TODO: Implement onError() method.
    }

    public function onMessage()
    {
        // TODO: Implement onMessage() method.
    }

}