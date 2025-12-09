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

use App\Kernel;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

new Dotenv()->bootEnv(dirname(__DIR__).'/.env');

/** @var string $environment */
$environment = $_SERVER['APP_ENV'];
$kernel = new Kernel($environment, (bool) $_SERVER['APP_DEBUG']);
// Usage of super global $_SERVER found; Usage of GLOBALS are discouraged
// consider not relying on global scope
$kernel->boot();

$doctrine = $kernel->getContainer()->get('doctrine');
/** @var Registry $doctrine_registry */
$doctrine_registry = $doctrine;

return $doctrine_registry->getManager();
