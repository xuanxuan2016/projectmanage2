<?php

namespace Framework\Facade;

/**
 * @method static string getImageBase64($strImagePath)
 * @method static string compressImage($strImagePath, $floatRatio = 1, $blnDel = true)
 * 
 * @see \Framework\Service\Image\Image
 */
class Image extends Facade {

    /**
     * 获取外观名称
     */
    protected static function getFacadeAccessor() {
        return 'image';
    }

}
