#!/bin/bash
set -e

echo "======================================"
echo "Building PHP 7.4 Compatible Version"
echo "======================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Base directory
BASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BUILD_DIR="${BASE_DIR}/build/php74"

echo -e "${BLUE}[1/6]${NC} Cleaning previous build..."
rm -rf "${BUILD_DIR}"
mkdir -p "${BUILD_DIR}"

echo -e "${GREEN}✓${NC} Build directory ready"
echo ""

echo -e "${BLUE}[2/6]${NC} Copying source files..."
cp -r "${BASE_DIR}/src" "${BUILD_DIR}/src"

echo -e "${GREEN}✓${NC} Source files copied"
echo ""

echo -e "${BLUE}[3/6]${NC} Running Rector transformation to PHP 7.4..."
echo -e "${YELLOW}This may take a few moments...${NC}"

# Run Rector on the copied files
"${BASE_DIR}/vendor/bin/rector" process "${BUILD_DIR}/src" \
	--config="${BASE_DIR}/rector.php" \
	--clear-cache \
	--no-progress-bar

echo -e "${GREEN}✓${NC} Rector transformation complete"
echo ""

echo -e "${BLUE}[4/6]${NC} Generating PHP 7.4 composer.json..."
php "${BASE_DIR}/scripts/generate-php74-composer.php"

echo -e "${GREEN}✓${NC} composer.json generated"
echo ""

echo -e "${BLUE}[5/6]${NC} Copying additional files..."
cp "${BASE_DIR}/LICENSE" "${BUILD_DIR}/" 2>/dev/null || echo "No LICENSE file found"
cp "${BASE_DIR}/README.md" "${BUILD_DIR}/" 2>/dev/null || echo "No README.md file found"

# Create build-specific README
cat > "${BUILD_DIR}/BUILD.md" << 'EOF'
# PHP 7.4 Build

This directory contains a PHP 7.4 compatible version of the MCP PHP SDK,
automatically transpiled from the PHP 8.1+ source using RectorPHP.

## ⚠️ Important

**Do not edit files in this directory directly.** This is a generated build.

To regenerate this build:

```bash
cd /path/to/php-sdk
bash scripts/build-php74.sh
```

## Installation

### Via Composer (Local Path)

In your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "/path/to/php-sdk/build/php74"
        }
    ],
    "require": {
        "mcp/server": "*"
    }
}
```

Then run:

```bash
composer install
```

## Key Differences from PHP 8.1+ Version

This transpiled version has the following changes:

- **No Attributes**: Use array-based registration instead
- **No Enums**: Converted to class constants
- **No Readonly Properties**: Use getter methods
- **No Union Types**: Documented in PHPDoc only
- **No Match Expressions**: Converted to switch statements
- **No Constructor Property Promotion**: Traditional constructor syntax

## Usage

The API remains largely the same, with the main difference being the registration of tools, resources, and prompts:

### Array-Based Registration (PHP 7.4)

```php
use Mcp\Server\Server;

$server = new Server($configuration);

// Register a tool
$server->registerTool([
    'name' => 'echo',
    'description' => 'Echoes back the input',
    'inputSchema' => [
        'type' => 'object',
        'properties' => [
            'message' => [
                'type' => 'string',
                'description' => 'Message to echo'
            ]
        ],
        'required' => ['message']
    ],
    'handler' => function(array $arguments) {
        return $arguments['message'] ?? '';
    }
]);

$server->run();
```

## Testing

To test this build:

```bash
cd build/php74
composer install
vendor/bin/phpunit
```

## Requirements

- PHP 7.4 or higher
- Composer

## Support

This is an automatically generated build. For issues, please refer to the main repository.

---

**Generated:** $(date)
**Source:** Main PHP 8.1+ codebase
**Build Tool:** RectorPHP
EOF

echo -e "${GREEN}✓${NC} Additional files copied"
echo ""

echo -e "${BLUE}[6/6]${NC} Validating composer.json..."
cd "${BUILD_DIR}"
if composer validate --no-check-publish --no-check-all; then
	echo -e "${GREEN}✓${NC} composer.json is valid"
else
	echo -e "${YELLOW}⚠${NC}  composer.json validation warning (non-critical)"
fi

echo ""
echo "======================================"
echo -e "${GREEN}✓ Build Complete!${NC}"
echo "======================================"
echo ""
echo "PHP 7.4 compatible version available at:"
echo "${BUILD_DIR}"
echo ""
echo "Next steps:"
echo "1. Review the transformed code"
echo "2. Run tests: cd ${BUILD_DIR} && composer install && vendor/bin/phpunit"
echo "3. Test with WordPress MCP adapter"
echo ""
