<?php
/**
* 2017 - Keyrnel SARL
*
* NOTICE OF LICENSE
*
* The source code of this module is under a commercial license.
* Each license is unique and can be installed and used on only one shop.
* Any reproduction or representation total or partial of the module, one or more of its components,
* by any means whatsoever, without express permission from us is prohibited.
* If you have not received this module from us, thank you for contacting us.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future.
*
* @author    Keyrnel
* @copyright 2017 - Keyrnel SARL
* @license   commercial
* International Registered Trademark & Property of Keyrnel SARL
*/

class GiftCardUploader
{
    const DEFAULT_TEMPLATE = 'simple.tpl';
    const DEFAULT_AJAX_TEMPLATE = 'ajax.tpl';

    const TYPE_IMAGE = 'image';
    const TYPE_FILE = 'file';

    private $_check_file_size;
    private $_accept_types;
    private $_max_size;
    private $_save_path;
    private $_context;
    private $_drop_zone;
    private $_id;
    private $_files;
    private $_name;
    private $_max_files;
    private $_multiple;
    private $_post_max_size;
    protected $_template;
    private $_template_directory;
    private $_title;
    private $_url;
    private $_use_ajax;

    public function __construct($name = null)
    {
        $this->setName($name);
        $this->setCheckFileSize(true);
        $this->files = array();
    }

    public function setAcceptTypes($value)
    {
        $this->_accept_types = $value;
        return $this;
    }

    public function getAcceptTypes()
    {
        return $this->_accept_types;
    }

    public function setCheckFileSize($value)
    {
        $this->_check_file_size = $value;
        return $this;
    }

    public function setMaxSize($value)
    {
        $this->_max_size = (int)$value;
        return $this;
    }

    public function setSavePath($value)
    {
        $this->_save_path = $value;
        return $this;
    }

    public function getPostMaxSizeBytes()
    {
        $post_max_size = ini_get('post_max_size');
        $bytes         = (int)trim($post_max_size);
        $last          = strtolower($post_max_size[strlen($post_max_size) - 1]);

        switch ($last) {
            case 'g': $bytes *= 1024;
            // no break
            case 'm': $bytes *= 1024;
            // no break
            case 'k': $bytes *= 1024;
        }

        if ($bytes == '') {
            $bytes = null;
        }
        return $bytes;
    }

    public function getUniqueFileName($prefix = 'PS')
    {
        return uniqid($prefix, true);
    }

    public function checkFileSize()
    {
        return (isset($this->_check_file_size) && $this->_check_file_size);
    }

    public function process($dest = null)
    {
        $upload = isset($_FILES[$this->getName()]) ? $_FILES[$this->getName()] : null;

        if ($upload && is_array($upload['tmp_name'])) {
            $tmp = array();
            foreach ($upload['tmp_name'] as $index => $value) {
                $tmp[$index] = array(
                    'tmp_name' => $upload['tmp_name'][$index],
                    'name'     => $upload['name'][$index],
                    'size'     => $upload['size'][$index],
                    'type'     => $upload['type'][$index],
                    'error'    => $upload['error'][$index]
                );

                $this->files[] = $this->upload($tmp[$index], $dest);
            }
        } elseif ($upload) {
            $this->files[] = $this->upload($upload, $dest);
        }

        return $this->files;
    }

    public function upload($file, $dest = null)
    {
        if ($this->validate($file)) {
            if (isset($dest) && is_dir($dest)) {
                $file_path = $dest;
            } else {
                $file_path = $this->getFilePath(isset($dest) ? $dest : $file['name']);
            }

            if ($file['tmp_name'] && is_uploaded_file($file['tmp_name'])) {
                move_uploaded_file($file['tmp_name'], $file_path);
            } else {
                // Non-multipart uploads (PUT method support)
                file_put_contents($file_path, fopen('php://input', 'r'));
            }

            $file_size = $this->_getFileSize($file_path, true);

            if ($file_size === $file['size']) {
                $file['save_path'] = $file_path;
            } else {
                $file['size'] = $file_size;
                unlink($file_path);
                $file['error'] = Tools::displayError('Server file size is different from local file size');
            }
        }

        return $file;
    }

    protected function checkUploadError($error_code)
    {
        $error = 0;
        switch ($error_code) {
            case 1:
            $error = sprintf(Tools::displayError('The uploaded file exceeds %s'), ini_get('upload_max_filesize'));
            break;
            case 2:
            $error = sprintf(Tools::displayError('The uploaded file exceeds %s'), ini_get('post_max_size'));
            break;
            case 3:
            $error = Tools::displayError('The uploaded file was only partially uploaded');
            break;
            case 4:
            $error = Tools::displayError('No file was uploaded');
            break;
            case 6:
            $error = Tools::displayError('Missing temporary folder');
            break;
            case 7:
            $error = Tools::displayError('Failed to write file to disk');
            break;
            case 8:
            $error = Tools::displayError('A PHP extension stopped the file upload');
            break;
            default:
            break;
        }
        return $error;
    }

    protected function _getFileSize($file_path, $clear_stat_cache = false)
    {
        if ($clear_stat_cache) {
            clearstatcache(true, $file_path);
        }

        return filesize($file_path);
    }

    protected function _getServerVars($var)
    {
        return (isset($_SERVER[$var]) ? $_SERVER[$var] : '');
    }

    protected function _normalizeDirectory($directory)
    {
        $last = $directory[Tools::strlen($directory) - 1];

        if (in_array($last, array('/', '\\'))) {
            $directory[Tools::strlen($directory) - 1] = DIRECTORY_SEPARATOR;
            return $directory;
        }

        $directory .= DIRECTORY_SEPARATOR;
        return $directory;
    }

    public function getMaxSize()
    {
        return Tools::getMaxUploadSize();
    }

    public function getSavePath()
    {
        return $this->_normalizeDirectory(_PS_TMP_IMG_DIR_);
    }

    public function getFilePath($file_name = null)
    {
        //Force file path
        return tempnam($this->getSavePath(), $this->getUniqueFileName());
    }

    public function setContext($value)
    {
        $this->_context = $value;
        return $this;
    }

    public function getContext()
    {
        if (!isset($this->_context)) {
            $this->_context = Context::getContext();
        }

        return $this->_context;
    }

    public function setDropZone($value)
    {
        $this->_drop_zone = $value;
        return $this;
    }

    public function getDropZone()
    {
        if (!isset($this->_drop_zone)) {
            $this->setDropZone("$('#".$this->getId()."-add-button')");
        }

        return $this->_drop_zone;
    }

    public function setId($value)
    {
        $this->_id = (string)$value;
        return $this;
    }

    public function getId()
    {
        if (!isset($this->_id) || trim($this->_id) === '') {
            $this->_id = $this->getName();
        }

        return $this->_id;
    }

    public function setFiles($value)
    {
        $this->_files = $value;
        return $this;
    }

    public function getFiles()
    {
        if (!isset($this->_files)) {
            $this->_files = array();
        }

        return $this->_files;
    }

    public function setMaxFiles($value)
    {
        $this->_max_files = isset($value) ? (int)$value : 5;
        return $this;
    }

    public function getMaxFiles()
    {
        return $this->_max_files;
    }

    public function setMultiple($value)
    {
        $this->_multiple = (bool)$value;
        return $this;
    }

    public function setName($value)
    {
        $this->_name = (string)$value;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setPostMaxSize($value)
    {
        $this->_post_max_size = $value;
        $this->setMaxSize($value);
        return $this;
    }

    public function getPostMaxSize()
    {
        if (!isset($this->_post_max_size)) {
            $this->_post_max_size = parent::getPostMaxSize();
        }

        return $this->_post_max_size;
    }

    public function setTemplate($value)
    {
        $this->_template = $value;
        return $this;
    }

    public function getTemplate()
    {
        if (!isset($this->_template)) {
            $this->setTemplate(self::DEFAULT_TEMPLATE);
        }

        return $this->_template;
    }

    public function setTemplateDirectory($value)
    {
        $this->_template_directory = $value;
        return $this;
    }

    public function getTemplateDirectory()
    {
        if (!isset($this->_template_directory)) {
            $this->_template_directory = _PS_MODULE_DIR_.'thegiftcard/views/templates/admin/uploader';
        }

        return $this->_normalizeDirectory($this->_template_directory);
    }

    public function getTemplateFile($template)
    {
        return $this->getTemplateDirectory().$template;
    }

    public function setTitle($value)
    {
        $this->_title = $value;
        return $this;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setUrl($value)
    {
        $this->_url = (string)$value;
        return $this;
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function setUseAjax($value)
    {
        $this->_use_ajax = (bool)$value;
        return $this;
    }

    public function isMultiple()
    {
        return (isset($this->_multiple) && $this->_multiple);
    }

    public function render()
    {
        $this->getContext()->controller->addJS(array(
            _MODULE_DIR_.'thegiftcard/views/js/tools/load-image.all.min.js',
            _MODULE_DIR_.'thegiftcard/views/js/tools/jquery.iframe-transport.js',
            _MODULE_DIR_.'thegiftcard/views/js/tools/jquery.fileupload.js',
            _MODULE_DIR_.'thegiftcard/views/js/tools/jquery.fileupload-process.js',
            _MODULE_DIR_.'thegiftcard/views/js/tools/jquery.fileupload-image.js',
            _MODULE_DIR_.'thegiftcard/views/js/tools/jquery.fileupload-validate.js',
            _MODULE_DIR_.'thegiftcard/views/js/tools/spin.js',
            _MODULE_DIR_.'thegiftcard/views/js/tools/ladda.js'
        ));

        if ($this->useAjax() && !isset($this->_template)) {
            $this->setTemplate(self::DEFAULT_AJAX_TEMPLATE);
        }

        $template = $this->getContext()->smarty->createTemplate($this->getTemplateFile($this->getTemplate()), $this->getContext()->smarty);

        $template->assign(array(
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'url'           => $this->getUrl(),
            'multiple'      => $this->isMultiple(),
            'files'         => $this->getFiles(),
            'title'         => $this->getTitle(),
            'max_files'     => $this->getMaxFiles(),
            'post_max_size' => $this->getPostMaxSizeBytes(),
            'drop_zone'     => $this->getDropZone()
        ));

        return $template->fetch();
    }

    public function useAjax()
    {
        return (isset($this->_use_ajax) && $this->_use_ajax);
    }

    protected function validate(&$file)
    {
        $file['error'] = $this->checkUploadError($file['error']);
        if ($file['error']) {
            return false;
        }

        $post_max_size = Tools::convertBytes(ini_get('post_max_size'));
        $upload_max_filesize = Tools::convertBytes(ini_get('upload_max_filesize'));

        if ($post_max_size && ($this->_getServerVars('CONTENT_LENGTH') > $post_max_size)) {
            $file['error'] = Tools::displayError('The uploaded file exceeds the post_max_size directive in php.ini');
            return false;
        }

        if ($upload_max_filesize && ($this->_getServerVars('CONTENT_LENGTH') > $upload_max_filesize)) {
            $file['error'] = Tools::displayError('The uploaded file exceeds the upload_max_filesize directive in php.ini');
            return false;
        }

        if ($error = ImageManager::validateUpload($file, Tools::getMaxUploadSize($this->getMaxSize()), $this->getAcceptTypes())) {
            $file['error'] = $error;
            return false;
        }

        if ($file['size'] > $this->getMaxSize()) {
            $file['error'] = sprintf(Tools::displayError('File (size : %1s) is too big (max : %2s)'), $file['size'], $this->getMaxSize());
            return false;
        }

        return true;
    }
}
