<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Doctrine\ORM;

use Doctrine\ORM\Configuration;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ConfigurationTest extends KernelTestCase
{
    private function getConfiguration(): Configuration
    {
        return self::getContainer()->get('doctrine.orm.default_configuration');
    }

    public function testNativeLazyObjectsSetting(): void
    {
        if (\PHP_VERSION_ID >= 80400) {
            $this->assertTrue($this->getConfiguration()->isNativeLazyObjectsEnabled());
        } else {
            $this->assertFalse($this->getConfiguration()->isNativeLazyObjectsEnabled());
        }
    }
}
