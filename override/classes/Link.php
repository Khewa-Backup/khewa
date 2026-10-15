<?php
if (!defined('_PS_VERSION_')) { exit; }
class Link extends LinkCore
{
    /*
    * module: ets_superspeed
    * date: 2026-01-17 12:14:10
    * version: 2.1.2
    */
    public function getImageLink($name, $ids, $type = null, $extension = 'jpg')
    {
        $is_webp = false;
        if (Tools::strpos($ids, 'default') !== false) {
            $uriPath = _THEME_PROD_DIR_ . $ids . ($type ? '-' . $type : '') . '.jpg';
            if(file_exists(_PS_PROD_IMG_DIR_ . $ids . ($type ? '-' . $type : '')  . '.webp'))
                $is_webp = true;
        } else {
            $splitIds = explode('-', $ids);
            $idImage = (isset($splitIds[1]) ? $splitIds[1] : $splitIds[0]);
            if ($this->allow == 1) {
                $uriPath = __PS_BASE_URI__ . $idImage . ($type ? '-' . $type : '')  . '/' . $name . '.jpg';
            } else {
                $uriPath = _THEME_PROD_DIR_ . Image::getImgFolderStatic($idImage) . $idImage . ($type ? '-' . $type : '')  . '.jpg';
            }
            if(file_exists(_PS_PROD_IMG_DIR_ . Image::getImgFolderStatic($idImage) . $idImage . ($type ? '-' . $type : '') . '.webp'))
                $is_webp = true;
        }
        if($is_webp)
        {
            $url = $this->protocol_content . Tools::getMediaServer($uriPath) . $uriPath;
            return str_replace('.jpg','.webp',$url);
        }
        else
            return $this->protocol_content . Tools::getMediaServer($uriPath) . $uriPath;
    }
}