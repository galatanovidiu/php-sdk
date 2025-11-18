<?php

declare(strict_types=1);

/**
 * Generate PHP 7.4 compatible composer.json
 *
 * This script reads the main composer.json and creates a modified version
 * for the PHP 7.4 build with downgraded dependencies.
 */

$base_dir = dirname(__DIR__);
$source_composer = $base_dir . '/composer.json';
$target_composer = $base_dir . '/build/php74/composer.json';

if (!file_exists($source_composer)) {
	fwrite(STDERR, "Error: composer.json not found at {$source_composer}\n");
	exit(1);
}

$composer = json_decode(file_get_contents($source_composer), true);

if ($composer === null) {
	fwrite(STDERR, "Error: Failed to parse composer.json\n");
	exit(1);
}

// Update PHP requirement to 7.4
$composer['require']['php'] = '^7.4 || ^8.0';

// Downgrade Symfony components to 5.4 LTS (last version supporting PHP 7.4)
if (isset($composer['require']['symfony/finder'])) {
	$composer['require']['symfony/finder'] = '^5.4';
}

if (isset($composer['require']['symfony/uid'])) {
	$composer['require']['symfony/uid'] = '^5.4';
}

// Downgrade PSR container
if (isset($composer['require']['psr/container'])) {
	$composer['require']['psr/container'] = '^1.1 || ^2.0';
}

// Add PHP 8.0 polyfills for string functions
$composer['require']['symfony/polyfill-php80'] = '^1.28';

// Downgrade dev dependencies
if (isset($composer['require-dev']['phpunit/phpunit'])) {
	$composer['require-dev']['phpunit/phpunit'] = '^9.5';
}

if (isset($composer['require-dev']['phpstan/phpstan'])) {
	$composer['require-dev']['phpstan/phpstan'] = '^1.10';
}

// Remove Rector from dev dependencies (not needed in distribution)
if (isset($composer['require-dev']['rector/rector'])) {
	unset($composer['require-dev']['rector/rector']);
}

// Add note about this being a generated file
$composer['description'] = ($composer['description'] ?? 'MCP PHP SDK') . ' (PHP 7.4 compatible build)';

// Add extra metadata
if (!isset($composer['extra'])) {
	$composer['extra'] = [];
}

$composer['extra']['build-info'] = [
	'type' => 'php74-transpiled',
	'generated' => date('Y-m-d H:i:s'),
	'source-version' => $composer['version'] ?? 'dev',
	'build-tool' => 'RectorPHP',
];

// Ensure build directory exists
$build_dir = dirname($target_composer);
if (!is_dir($build_dir)) {
	mkdir($build_dir, 0755, true);
}

// Write the modified composer.json
$json_options = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
$result = file_put_contents(
	$target_composer,
	json_encode($composer, $json_options) . "\n"
);

if ($result === false) {
	fwrite(STDERR, "Error: Failed to write composer.json to {$target_composer}\n");
	exit(1);
}

echo "✓ Generated PHP 7.4 composer.json at: {$target_composer}\n";
echo "\nKey changes:\n";
echo "  • PHP requirement: {$composer['require']['php']}\n";

if (isset($composer['require']['symfony/finder'])) {
	echo "  • symfony/finder: {$composer['require']['symfony/finder']}\n";
}

if (isset($composer['require']['symfony/uid'])) {
	echo "  • symfony/uid: {$composer['require']['symfony/uid']}\n";
}

echo "  • Added: symfony/polyfill-php80\n";

if (isset($composer['require-dev']['phpunit/phpunit'])) {
	echo "  • phpunit/phpunit: {$composer['require-dev']['phpunit/phpunit']}\n";
}

echo "\n";

exit(0);
