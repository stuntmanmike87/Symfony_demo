<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;

/**
 * @see \Rector\Config\RectorConfig::symfonyContainerXml()
 */
return static function (RectorConfig $rectorConfig): void {
    // $rectorConfig->sets([
    //     SetList::RECTOR_CONFIG
    // ]);

    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);

    //$rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');

    // basic rules
    $rectorConfig->importNames();
    $rectorConfig->importShortClasses();

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        //SetList::DEAD_CODE,
        // SetList::EARLY_RETURN,
        // SetList::INSTANCEOF,
        // SetList::NAMING,
        SetList::PHP_83,
        // SetList::PRIVATIZATION,
        // SetList::STRICT_BOOLEANS,
        // SetList::TYPE_DECLARATION,
        LevelSetList::UP_TO_PHP_83,
    ]);

    // symfony rules
    /* $rectorConfig->symfonyContainerPhp(
        __DIR__ . '/var/cache/website/dev/App_KernelDevDebugContainer.xml'
    );

    $rectorConfig->symfonyContainerXml(
        __DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml'
    ); */

    /* $rectorConfig->import(SymfonySetList::SYMFONY_63);

    $parameters = $rectorConfig->parameters();
    
    $parameters->set(Option::SOURCE, [__DIR__ . '/src']);

    $parameters->set(
        Option::class,
        __DIR__ . '/var/cache/dev/AppKernelDevDebugContainer.xml'
    ); */

    $rectorConfig->sets([
        //SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::SYMFONY_64,//SYMFONY_70,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ]);

        // doctrine rules
        $rectorConfig->sets([
            DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
            DoctrineSetList::DOCTRINE_CODE_QUALITY,
        ]);
    
        // phpunit rules
        $rectorConfig->sets([
            //PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
            PHPUnitSetList::PHPUNIT_100,
            PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        ]);

    /* $rectorConfig->skip([
        VarConstantCommentRector::class,
    ]); */

    /* $rectorConfig->ruleWithConfiguration(ChangeFileLoaderInExtensionAndKernelRector::class, [
        ChangeFileLoaderInExtensionAndKernelRector::FROM => 'yaml',
        ChangeFileLoaderInExtensionAndKernelRector::TO => 'php',
    ]); */
};
