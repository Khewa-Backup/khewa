<?php
/**
 * PrestaChamps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact support@prestachamps.com
 *
 * @author    PrestaChamps <support@prestachamps.com>
 * @copyright PrestaChamps
 * @license   commercial
 */

namespace PrestaChamps\PrestaShop\Override;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ConflictChecker
{
    /**
     * Module instance.
     *
     * @var \Module
     */
    protected $module;

    /**
     * Path to the pending overrides directory.
     *
     * @var string
     */
    protected $pendingOverridesDir;

    /**
     * Path to the PrestaShop overrides directory.
     *
     * @var string
     */
    protected $psOverridesDir;

    /**
     * Marker comment to identify our module's overrides.
     *
     * @var string
     */
    protected $moduleMarker;

    /**
     * Overrides array to identify our module's overrides.
     *
     * @var string[]
     */
    protected $overridesToCheck;

    /**
     * Constructor.
     *
     * @param \Module $module The module instance
     * @param string $pendingOverridesDir Path to pending overrides directory
     */
    public function __construct(\Module $module, $pendingOverridesDir = null)
    {
        $this->module = $module;
        $this->pendingOverridesDir = $pendingOverridesDir ?: _PS_MODULE_DIR_ . $this->module->name . '/overrides_pending/';
        $this->psOverridesDir = _PS_OVERRIDE_DIR_;
        $this->moduleMarker = '/* ' . $this->module->name . ' */';
        $this->overridesToCheck = [
            'classes/Cart.php',
            'classes/CartRule.php',
        ];
    }

    /**
     * Check if an override file exists in PrestaShop override directory.
     *
     * @param string $relativePath Relative path to the override file (e.g., 'classes/Cart.php')
     *
     * @return bool True if override exists, false otherwise
     */
    public function overrideExists($relativePath)
    {
        return file_exists($this->psOverridesDir . $relativePath);
    }

    /**
     * Check if our custom code/marker is already installed in an override.
     *
     * @param string $relativePath Relative path to the override file
     *
     * @return bool True if marker exists, false otherwise
     */
    public function hasCustomCode($relativePath)
    {
        if (!$this->overrideExists($relativePath)) {
            return false;
        }

        $content = file_get_contents($this->psOverridesDir . $relativePath);

        return false !== strpos($content, $this->moduleMarker);
    }

    /**
     * Detect if there's a real conflict and return detailed information.
     *
     * @param string $relativePath Relative path to the override file
     *
     * @return array Returns conflict details or success response
     */
    public function hasConflict($relativePath)
    {
        // If the override file doesn't exist in PrestaShop, no conflict
        $existingFile = $this->psOverridesDir . $relativePath;
        if (!file_exists($existingFile)) {
            return [
                'status' => 'success',
                'message' => 'No conflict detected',
            ];
        }

        // If our pending override doesn't exist, no conflict
        $pendingFile = $this->pendingOverridesDir . $relativePath;
        if (!file_exists($pendingFile)) {
            return [
                'status' => 'success',
                'message' => 'No pending override to install',
            ];
        }

        $existing = $this->parseClass($existingFile);
        $pending = $this->parseClass($pendingFile);

        if (!$existing['class'] || !$pending['class']) {
            return [
                'status' => 'conflict',
                'message' => 'Unable to determine class names',
                'file' => $relativePath,
            ];
        }

        $conflictingMethods = $this->collidingMethods($pending['methods'], $existing['methods']);
        if (!empty($conflictingMethods)) {
            return [
                'status' => 'conflict',
                'message' => 'Method conflicts detected',
                'file' => $relativePath,
                'class' => $existing['class'],
                'methods' => $conflictingMethods,
            ];
        }

        return [
            'status' => 'success',
            'message' => 'No conflicts detected',
            'file' => $relativePath,
        ];
    }

    /**
     * Method names declared in both overrides. Magic methods are excluded — both
     * files legitimately define them — and matching is case-insensitive because
     * PHP method names are.
     *
     * @param string[] $pendingMethods
     * @param string[] $existingMethods
     *
     * @return string[] names as spelled in the pending override
     */
    protected function collidingMethods(array $pendingMethods, array $existingMethods)
    {
        $ignored = [
            '__construct', '__destruct', '__call', '__get', '__set',
            '__isset', '__unset', '__tostring', '__invoke', '__clone',
        ];
        $existingLower = array_map('strtolower', $existingMethods);

        $colliding = [];
        foreach ($pendingMethods as $method) {
            $lower = strtolower($method);
            if (in_array($lower, $ignored, true)) {
                continue;
            }
            if (in_array($lower, $existingLower, true)) {
                $colliding[] = $method;
            }
        }

        return $colliding;
    }

    /**
     * Read a PHP file's class name and the methods declared in its body.
     *
     * Tokenised rather than evaluated: these files are arbitrary shop overrides,
     * so evaluating them would run their top-level code, and would throw
     * \ParseError — not an \Exception — on any file that doesn't parse.
     *
     * Methods reached through a trait are not seen, only ones declared in the body.
     *
     * @param string $file
     *
     * @return array{class: string|null, methods: string[]}
     */
    protected function parseClass($file)
    {
        $result = ['class' => null, 'methods' => []];

        $code = @file_get_contents($file);
        if (false === $code) {
            return $result;
        }

        $tokens = @token_get_all($code);
        if (!is_array($tokens)) {
            return $result;
        }

        $depth = 0;
        $inClassBody = false;
        $total = count($tokens);

        for ($i = 0; $i < $total; ++$i) {
            $token = $tokens[$i];

            if (!is_array($token)) {
                if ('{' === $token) {
                    ++$depth;
                } elseif ('}' === $token) {
                    --$depth;
                }
                continue;
            }

            if (T_CURLY_OPEN === $token[0] || T_DOLLAR_OPEN_CURLY_BRACES === $token[0]) {
                ++$depth;
                continue;
            }

            if (null === $result['class'] && T_CLASS === $token[0]) {
                // Skip `Foo::class`, which tokenises as T_CLASS too.
                $prev = $this->previousMeaningfulToken($tokens, $i);
                if (is_array($prev) && T_DOUBLE_COLON === $prev[0]) {
                    continue;
                }
                $next = $this->nextMeaningfulToken($tokens, $i);
                if (is_array($next) && T_STRING === $next[0]) {
                    $result['class'] = $next[1];
                    $inClassBody = true;
                }
                continue;
            }

            if ($inClassBody && 1 === $depth && T_FUNCTION === $token[0]) {
                $next = $this->nextMeaningfulToken($tokens, $i);
                if (is_array($next) && T_STRING === $next[0]) {
                    $result['methods'][] = $next[1];
                }
            }
        }

        return $result;
    }

    /**
     * @param int $index
     *
     * @return array|string|null
     */
    private function nextMeaningfulToken(array $tokens, $index)
    {
        $total = count($tokens);
        for ($i = $index + 1; $i < $total; ++$i) {
            if (is_array($tokens[$i]) && in_array($tokens[$i][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            return $tokens[$i];
        }

        return null;
    }

    /**
     * @param int $index
     *
     * @return array|string|null
     */
    private function previousMeaningfulToken(array $tokens, $index)
    {
        for ($i = $index - 1; $i >= 0; --$i) {
            if (is_array($tokens[$i]) && in_array($tokens[$i][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            return $tokens[$i];
        }

        return null;
    }

    /**
     * Get the module marker.
     *
     * @return string
     */
    public function getModuleMarker()
    {
        return $this->moduleMarker;
    }

    /**
     * Get list of all pending overrides.
     *
     * @return array List of relative paths to pending overrides
     */
    public function getPendingOverrides()
    {
        $overrides = [];

        foreach ($this->overridesToCheck as $relativePath) {
            $fullPath = $this->pendingOverridesDir . $relativePath;
            if (file_exists($fullPath)) {
                $overrides[] = $relativePath;
            }
        }

        return $overrides;
    }

    /**
     * Install a single override file.
     *
     * @param string $relativePath Relative path to the override file
     *
     * @return bool True if installation was successful
     */
    protected function installSingleOverride($relativePath)
    {
        $sourceFile = $this->pendingOverridesDir . $relativePath;
        $targetFile = $this->psOverridesDir . $relativePath;

        // Create directory if it doesn't exist
        $targetDir = dirname($targetFile);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Add our marker to the file content if it doesn't already have it
        $content = file_get_contents($sourceFile);
        if (false === strpos($content, $this->moduleMarker)) {
            $content = $this->moduleMarker . "\n" . $content;
        }

        // Write the file
        return false !== file_put_contents($targetFile, $content);
    }
}
