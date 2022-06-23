<?php

namespace Pantheon\UserBundle;

use Pantheon\UserBundle\DependencyInjection\UserBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class UserBundle extends Bundle
{
    public function getContainerExtension() : UserBundleExtension
    {
//        dump('r442342');
//        die();
        if ($this->extension === null) {
            $this->extension = new UserBundleExtension();
        }
        return $this->extension;
    }

    public function test()
    {
        echo "test";
        die();
    }


}
