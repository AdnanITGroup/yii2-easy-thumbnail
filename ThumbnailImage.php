<?php
/**
 * @link https://github.com/AdnanITGroup/yii2-easy-thumbnail
 * @copyright Copyright (c) 2015 Adnan IT Group
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace AdnanITGroup\thumbnail;

use Yii;
use yii\helpers\Html;
use yii\helpers\FileHelper;
use yii\imagine\Image;

/**
 * Yii2 helper for creating and caching thumbnails on real time in easy way  & force it to the exact size
 * @author Adnan IT Group
 * @package AdnanITGroup\thumbnail
 */
class ThumbnailImage
{

    /** @var string $cacheAlias path alias relative with @web where the cache files are kept */
    public static $cacheAlias = 'assets/sthumbnails';

    /** @var int $cacheExpire */
    public static $cacheExpire = 0;

    /**
     * Creates and caches the image thumbnail and returns ImageInterface.
     *
     * @param string $filename the image file path or path alias
     * @param integer $width the width in pixels to create the thumbnail
     * @param integer $height the height in pixels to create the thumbnail
     * @param string $quality of the output
     * @return \Imagine\Image\ImageInterface
     */
    public static function thumbnail($filename, $width, $height, $quality = 80)
    {
        return self::thumbnailFile($filename, $width, $height, $quality);
    }

    /**
     * Creates and caches the image thumbnail and returns full path from thumbnail file.
     *
     * @param string $filename
     * @param integer $width
     * @param integer $height
     * @param string $quality
     * @return string
     * @throws FileNotFoundException
     */
    public static function thumbnailFile($filename, $width, $height, $quality = 80)
    {
        $filename = FileHelper::normalizePath(Yii::getAlias($filename));
        if (!is_file($filename)) {
            throw new FileNotFoundException("File $filename doesn't exist");
        }
        $cachePath = Yii::getAlias('@webroot/' . self::$cacheAlias);

        $thumbnailFileExt = strrchr($filename, '.');
        $thumbnailFileName = md5($filename . $width . $height . filemtime($filename));
        $thumbnailFilePath = $cachePath . DIRECTORY_SEPARATOR . substr($thumbnailFileName, 0, 2);
        $thumbnailFile = $thumbnailFilePath . DIRECTORY_SEPARATOR . $thumbnailFileName . $thumbnailFileExt;

        if (file_exists($thumbnailFile)) {
            if (self::$cacheExpire !== 0 && (time() - filemtime($thumbnailFile)) > self::$cacheExpire) {
                unlink($thumbnailFile);
            } else {
                return $thumbnailFile;
            }
        }
        if (!is_dir($thumbnailFilePath)) {
            mkdir($thumbnailFilePath, 0755, true);
        }

        /*$box = new Box($width, $height);
        $image = Image::getImagine()->open($filename);
        $image = $image->thumbnail($box);

        $image->save($thumbnailFile);
        */

        Image::thumbnail($filename, $width, $height)
            ->save($thumbnailFile, ['quality' => $quality]);

        return $thumbnailFile;
    }

    /**
     * Creates and caches the image thumbnail and returns URL from thumbnail file.
     *
     * @param string $filename
     * @param integer $width
     * @param integer $height
     * @param string $quality
     * @return string
     */
    public static function thumbnailFileUrl($filename, $width, $height, $quality = 80)
    {
        $filename = FileHelper::normalizePath(Yii::getAlias($filename));
        $cacheUrl = Yii::getAlias('@web/' . self::$cacheAlias);
        $thumbnailFilePath = self::thumbnailFile($filename, $width, $height, $quality);

        preg_match('#[^\\' . DIRECTORY_SEPARATOR . ']+$#', $thumbnailFilePath, $matches);
        $fileName = $matches[0];

        return $cacheUrl . '/' . substr($fileName, 0, 2) . '/' . $fileName;
    }

    /**
     * Creates and caches the image thumbnail and returns <img> tag.
     *
     * @param string $filename
     * @param integer $width
     * @param integer $height
     * @param string $quality
     * @param array $options options similarly with \yii\helpers\Html::img()
     * @return string
     */
    public static function thumbnailImg($filename, $width, $height, $quality, $options = [])
    {
        $filename = FileHelper::normalizePath(Yii::getAlias($filename));
        try {
            $thumbnailFileUrl = self::thumbnailFileUrl($filename, $width, $height, $quality);
        } catch (FileNotFoundException $e) {
            return 'File doesn\'t exist';
        } catch (\Exception $e) {
            Yii::warning("{$e->getCode()}\n{$e->getMessage()}\n{$e->getFile()}");
            return 'Error ' . $e->getCode();
        }

        return Html::img(
            $thumbnailFileUrl,
            $options
        );
    }

    /**
     * Clear cache directory.
     *
     * @return bool
     */
    public static function clearCache()
    {
        $cacheDir = Yii::getAlias('@webroot/' . self::$cacheAlias);
        self::removeDir($cacheDir);
        return @mkdir($cacheDir, 0755, true);
    }

    protected static function removeDir($path)
    {
        if (is_file($path)) {
            @unlink($path);
        } else {
            array_map('self::removeDir', glob($path . DIRECTORY_SEPARATOR . '*'));
            @rmdir($path);
        }
    }
}
