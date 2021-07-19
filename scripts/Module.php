<?php
namespace scripts;

use vendor\base\BaseModule;

/**
 * 定时脚本
 * Class Module
 * @package scripts
 */
final class Module extends BaseModule
{
    public static function getModuleName($moduleRoute = null)
    {
        return __NAMESPACE__;
    }
}