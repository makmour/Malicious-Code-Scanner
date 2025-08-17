<?php
declare(strict_types=1);

namespace MCS;

interface NotifierInterface
{
    /**
     * @param array<int,array<string,mixed>> $findings
     */
    public function notify(array $findings): void;
}
