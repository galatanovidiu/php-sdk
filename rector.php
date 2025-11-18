<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/src',
	])
	->withSkip([
		// Skip vendor directory
		__DIR__ . '/vendor',
		// Skip rules that would add PHP 8+ features
		AddVoidReturnTypeWhereNoReturnRector::class,
		ClassPropertyAssignToConstructorPromotionRector::class,
		// Skip problematic hash algorithm rule
		\Rector\DowngradePhp81\Rector\FuncCall\DowngradeHashAlgorithmXxHashRector::class,
	])
	->withPhpSets(
		// Target PHP 7.4
		php74: true
	)
	// Downgrade from PHP 8.1 to PHP 7.4 (modern syntax replaces deprecated DowngradeLevelSetList)
	->withDowngradeSets(
		php74: true
	)
	->withPreparedSets(
		deadCode: true,
		codeQuality: true,
		typeDeclarations: false // Disable to avoid adding PHP 8+ type declarations
	)
	->withImportNames(
		importShortClasses: false,
		removeUnusedImports: true
	)
	->withParallel()
	->withCache(
		cacheDirectory: __DIR__ . '/rector-cache'
	);
