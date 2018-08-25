<?php
/**
 * Created by PhpStorm.
 * User: marin
 * Date: 2018/8/14
 * Time: 15:22
 */
namespace Marin\Phpwechat\Facades;
use Illuminate\Support\Facades\Facade;
class Phpwechat extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'phpwechat';
    }
}