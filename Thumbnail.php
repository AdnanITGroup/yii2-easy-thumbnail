<?php
/**
 * @link https://github.com/AdnanITGroup/yii2-easy-thumbnail
 * @copyright Copyright (c) 2015 Adnan IT Group
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace AdnanITGroup\thumbnail;

use yii\base\Object;

/**
 * EasyThumbnailImage global configuration component.
 *
 * @author Adnan IT Group
 * @package AdnanITGroup\thumbnail
 */
class Thumbnail extends Object
{
    /** @var string $cacheAlias path alias relative with @web where the cache files are kept */
    public $cacheAlias = 'assets/sthumbnails';

    /** @var int $cacheExpire seconds */
    public $cacheExpire = 0;

    public function init()
    {
        ThumbnailImage::$cacheAlias = $this->cacheAlias;
        ThumbnailImage::$cacheExpire = $this->cacheExpire;
    }
}
