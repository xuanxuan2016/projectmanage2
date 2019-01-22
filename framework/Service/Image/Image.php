<?php

namespace Framework\Service\Image;

use Framework\Facade\App;

class Image {

    /**
     * 获取图片的64位编码信息
     * @param string $strImagePath 图片地址
     * @return array 图片信息，读取错误返回空<br>
     * width:图片宽度
     * height:图片高度
     * encode:图片编码
     */
    public function getImageBase64($strImagePath) {
        $arrReturn = array('width' => 0, 'height' => 0, 'encode' => '');
        //1.图片是否存在
        if (!file_exists($strImagePath)) {
            return $arrReturn;
        }
        $arrImageInfo = getimagesize($strImagePath);

        //2.图片读取错误
        if ($arrImageInfo === false) {
            return $arrReturn;
        }

        //3.图片读取正确
        $strImageData = fread(fopen($strImagePath, 'r'), filesize($strImagePath));
        $strBase64image = 'data:' . $arrImageInfo['mime'] . ';base64,' . chunk_split(base64_encode($strImageData));
        $arrReturn['width'] = $arrImageInfo[0];
        $arrReturn['height'] = $arrImageInfo[1];
        $arrReturn['encode'] = $strBase64image;
        return $arrReturn;
    }

    /**
     * 图片压缩
     * @param string $strImagePath 需要压缩的图片地址
     * @param float $floatRatio 压缩比例，默认为1
     * @param bool $blnDel 是否删除原图，默认为true
     * @return string 返回新的图片地址
     */
    public function compressImage($strImagePath, $floatRatio = 1, $blnDel = true) {
        //1.图片是否存在
        if (!file_exists($strImagePath)) {
            return $arrReturn;
        }
        $arrImageInfo = getimagesize($strImagePath);

        //2.获取原图片标识符
        $objImage = false;
        $strExt = explode('/', $arrImageInfo['mime'])[1];
        switch ($strExt) {
            case 'png':
                $objImage = imagecreatefrompng($strImagePath);
                break;
            case 'jpeg':
                $objImage = imagecreatefromjpeg($strImagePath);
                break;
            case 'jpg':
                $objImage = imagecreatefromjpeg($strImagePath);
                break;
            case 'gif':
                $objImage = imagecreatefromgif($strImagePath);
                break;
        }
        if (!$objImage) {
            return '';
        }

        //3.创建新图
        $intSrcWidth = imagesx($objImage);
        $intSrcHeight = imagesy($objImage);
        $intDstWidth = round($intSrcWidth * $floatRatio);
        $intDstHeight = round($intSrcHeight * $floatRatio);
        $objImgNew = imagecreatetruecolor($intDstWidth, $intDstHeight);
        //分配颜色 + alpha,将颜色填充到新图上
        $intAlpha = imagecolorallocatealpha($objImgNew, 0, 0, 0, 127);
        imagefill($objImgNew, 0, 0, $intAlpha);

        //4.将源图拷贝到新图上，并设置在保存 PNG 图像时保存完整的 alpha 通道信息
        $strImagePathNew = App::make('path.storage') . '/cache/image/' . getGUID() . '.png';
        imagecopyresampled($objImgNew, $objImage, 0, 0, 0, 0, $intDstWidth, $intDstHeight, $intSrcWidth, $intSrcHeight);
        imagesavealpha($objImgNew, true);
        imagepng($objImgNew, $strImagePathNew);

        //5.删除原图
        if ($blnDel) {
            unlink($strImagePath);
        }

        //6.返回
        return $strImagePathNew;
    }

}
