<?php

namespace Framework\Facade;

/**
 * @method static string getDirPath($strDirPath)
 * 
 * @see \Framework\Service\File\File
 */
class File extends Facade {

    /**
     * 获取外观名称
     */
    protected static function getFacadeAccessor() {
        return 'file';
    }

}
