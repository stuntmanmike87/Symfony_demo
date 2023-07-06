<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector;
use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Rector\Set\ValueObject\LevelSetList;
//use Rector\Symfony\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector;
use Rector\Symfony\Set\SymfonySetList;

/**
 * @see \Rector\Config\RectorConfig::symfonyContainerXml()
 */
return static function (RectorConfig $rectorConfig): void {
    // $rectorConfig->sets([
    //     SetList::RECTOR_CONFIG
    // ]);

    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);

    $rectorConfig->symfonyContainerXml(
        __DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml'
    );

    $rectorConfig->import(SymfonySetList::SYMFONY_63);

    $parameters = $rectorConfig->parameters();
    
    $parameters->set(Option::SOURCE, [__DIR__ . '/src']);

    $parameters->set(
        Option::class,
        __DIR__ . '/var/cache/dev/AppKernelDevDebugContainer.xml'
    );

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        //SetList::DEAD_CODE,
        SetList::PHP_82,
        // SetList::ACTION_INJECTION_TO_CONSTRUCTOR_INJECTION,
        // SetList::EARLY_RETURN,
        // SetList::INSTANCEOF,
        // SetList::PRIVATIZATION,
        // SetList::TYPE_DECLARATION,
        // SetList::PSR_4,
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82
    ]);

    $rectorConfig->sets([
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::SYMFONY_63,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ]);

    $rectorConfig->skip([
        VarConstantCommentRector::class,
    ]);

    /* $rectorConfig->ruleWithConfiguration(ChangeFileLoaderInExtensionAndKernelRector::class, [
        ChangeFileLoaderInExtensionAndKernelRector::FROM => 'yaml',
        ChangeFileLoaderInExtensionAndKernelRector::TO => 'php',
    ]); */
};
