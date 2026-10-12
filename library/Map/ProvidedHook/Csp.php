<?php

namespace Icinga\Module\Map\ProvidedHook;

use Icinga\Application\Hook\CspHook;
use ipl\Web\Common\Csp as CspInstance;
use Icinga\User;

class Csp extends CspHook
{
    public function getCspForAllUsers(): CspInstance
    {
        $csp = new CspInstance();
        $csp->add('img-src', '*.openstreetmap.org');
        return $csp;
    }

    public function getCspForUser(User $user): CspInstance
    {
        return $this->getCspForAllUsers();
    }
}
