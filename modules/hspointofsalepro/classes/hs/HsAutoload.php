<?php
/**
 * Hamsa PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */

/**
 * Autoloader
 * HsAutoload is designed as a callback so that we can hook into spl_autoload_register().
 * Read more at: http://php.net/manual/en/function.spl-autoload-register.php
 * How it works?
 * - Scan for all files under the directories [root_dir]/directory(ies)/ and sub-directories recursively
 * - Generate an index (of all found classes), store in a file in php format
 * - Whenever a new class is retrieved by programs, if that class is not registered, the system will utilize HsAutoload to locate and load an associated file (if any).
 * - HsAutoload is helpful for loading any kind of classes under the specific directories automatically.
 *
 * @version 1.1
 *
 * @changelog
 * version 1.1
 * - Don't redeclare class "HsAutoload" if it's already loaded
 *
 * version 1.0
 * - Initial
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
if (!class_exists('HsAutoload')) {
    class HsAutoload
    {
        /**
         * A unique name of application which utilises this Autoloader.
         *
         * @var string
         */
        protected $name;

        /**
         * File where classes index is stored; mostlikely, it's stored under "[webroot]/" directory.
         *
         * @var string
         */
        protected $index_file;

        /**
         * Name of directory where index file is stored.
         *
         * @var string
         */
        protected $index_directory = 'cache';

        /**
         * List of directories where all kinds of classes could be found.
         *
         * @var array
         *            <pre>
         *            array(
         *            string, // relavtive path to the directory
         *            string,
         *            ...
         *            )
         */
        protected $scanned_directories = array();

        /**
         * @var array
         *            <pre>
         *            array(
         *            HsAutoload,
         *            HsAutoload,
         *            HsAutoload,
         *            ...
         *            )
         */
        protected static $instances;

        /**
         * @see self::getSingleton(), params of this __construct() matched with getSingleton()
         */
        protected function __construct($name, $version, $root_dir, array $scanned_dirs, $is_upgrade = false)
        {
            $this->setName($name);
            $this->scanned_directories = $scanned_dirs;
            $this->root_dir = realpath($root_dir) . '/';
            $this->index_file = $this->getName() . '_' . $version . '.php';
            $this->loadClassIndex($is_upgrade);
        }

        /**
         * Set a unique name, this is optional but highly recommended to set your own name to avoid conflict.
         *
         * @param string $name
         *
         * @return HsAutoload
         */
        protected function setName($name)
        {
            $this->name = $name;

            return $this;
        }

        protected function getName()
        {
            return !empty($this->name) ? $this->name : $this->generateUniqueKey();
        }

        /**
         * @return string
         */
        protected function generateUniqueKey()
        {
            return md5(realpath(__FILE__));
        }

        protected function loadClassIndex($is_upgrade = false)
        {
            $file = $this->getCacheDirectoryPath($is_upgrade) . $this->index_file;
            if ($is_upgrade || !(@filemtime($file) && is_readable($file))) {
                $this->removeIndexes();
                $this->generateIndex();
            } else {
                $this->index = include $file;
            }
        }

        /**
         * @return string
         */
        protected function getCacheDirectoryPath($is_upgrade = false)
        {
            return defined('_PS_CACHE_DIR_') && $is_upgrade ? $this->normalizeDirectory(_PS_ROOT_DIR_) . $this->index_directory . DIRECTORY_SEPARATOR : $this->normalizeDirectory(_PS_CACHE_DIR_);
        }

        /**
         * @see parent::generateIndex()
         */
        public function generateIndex()
        {
            $classes = array();
            foreach ($this->scanned_directories as $directory) {
                $classes = array_merge($classes, $this->getClassesFromDir($directory));
            }
            ksort($classes);
            $content = '<?php return ' . var_export($classes, true) . '; ?>';

            $filename = $this->getCacheDirectoryPath() . $this->index_file;
            $tmp_filename = tempnam(dirname($filename), basename($filename . '.'));
            if ($tmp_filename !== false && file_put_contents($tmp_filename, $content) !== false) {
                if (!@rename($tmp_filename, $filename) && file_exists($tmp_filename)) {
                    unlink($tmp_filename);
                } else {
                    @chmod($filename, 0666);
                }
            } else {
                Tools::error_log('Cannot write temporary file ' . $tmp_filename); // translation is not required here
            }
            $this->index = $classes;
        }

        protected function removeIndexes()
        {
            foreach (scandir($this->getCacheDirectoryPath()) as $file) {
                $current_file = $this->getCacheDirectoryPath() . $file;
                if (strpos($file, $this->getName()) === 0 && file_exists($current_file)) {
                    unlink($current_file);
                }
            }
        }

        /**
         * Make sure the directory ends with "/" or "\".
         *
         * @param string $directory
         *
         * @return string
         * @see parent::normalizeDirectory()
         *
         */
        protected function normalizeDirectory($directory)
        {
            return rtrim($directory, '/\\') . DIRECTORY_SEPARATOR;
        }

        /**
         * Get instance of autoload (singleton).
         *
         * @param string $name unique name of this instance
         * @param string $version version of application which utilises this autoloader
         * @param string $root_dir
         * @param array $scanned_dirs Relative paths to directories where classes are loaded from
         *                             <pre>
         *                             array(
         *                             string,
         *                             string
         *                             )
         *
         * @return HsAutoload
         */
        public static function getSingleton($name, $version, $root_dir, array $scanned_dirs, $is_upgrade = false)
        {
            if ($is_upgrade = true || empty(self::$instances) || empty(self::$instances[$name])) {
                self::$instances[$name] = new self($name, $version, $root_dir, $scanned_dirs, $is_upgrade);
            }

            return self::$instances[$name];
        }

        /**
         * @param string $classname
         */
        public function load($classname)
        {
            if (isset($this->index[$classname]) && $this->index[$classname]['path'] && !is_file($this->root_dir . $this->index[$classname]['path'])) {
                $this->generateIndex();
            }
            if (isset($this->index[$classname]['path']) && $this->index[$classname]['path']) {
                require_once $this->root_dir . $this->index[$classname]['path'];
            }
        }
        protected function getClassesFromDir($path)
        {
            $rootDir = $this->root_dir;
            if (!is_dir($rootDir . $path)) {
                return [];
            }

            $classes = [];
            foreach (scandir($rootDir . $path, SCANDIR_SORT_NONE) as $file) {
                if ($file[0] != '.') {
                    if (is_dir($rootDir . $path . $file)) {
                        $classes = array_merge($classes, $this->getClassesFromDir($path . $file . '/'));
                    } elseif (substr($file, -4) == '.php') {
                        $content = file_get_contents($rootDir . $path . $file);

                        $namePattern = '[a-z_\x7f-\xff][a-z0-9_\x7f-\xff]*';
                        $nameWithNsPattern = '(?:\\\\?(?:' . $namePattern . '\\\\)*' . $namePattern . ')';
                        $pattern = '~(?<!\w)((abstract\s+)?class|interface)\s+(?P<classname>' . basename($file, '.php') . '(?:Core)?)'
                                    . '(?:\s+extends\s+' . $nameWithNsPattern . ')?(?:\s+implements\s+' . $nameWithNsPattern . '(?:\s*,\s*' . $nameWithNsPattern . ')*)?\s*\{~i';

                        //DONT LOAD CLASS WITH NAMESPACE - PSR4 autoloaded from composer
                        $usesNamespace = false;
                        foreach (token_get_all($content) as $token) {
                            if ($token[0] === T_NAMESPACE) {
                                $usesNamespace = true;

                                break;
                            }
                        }

                        if (!$usesNamespace && preg_match($pattern, $content, $m)) {
                            $classes[$m['classname']] = [
                                'path' => $path . $file,
                                'type' => trim($m[1]),
                            ];

                            if (substr($m['classname'], -4) == 'Core') {
                                $classes[substr($m['classname'], 0, -4)] = [
                                    'path' => '',
                                    'type' => $classes[$m['classname']]['type'],
                                ];
                            }
                        }
                    }
                }
            }

            return $classes;
        }
    }
}
