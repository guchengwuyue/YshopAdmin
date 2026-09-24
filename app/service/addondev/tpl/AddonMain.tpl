<?php
declare(strict_types=1);

namespace addons\{$name};

use app\common\addon\Addon;

/**
 * {$title}
 */
class {$Name} extends Addon
{
    /**
     * install
     */
    public function install(): bool
    {
        return true;
    }

    /**
     * uninstall
     */
    public function uninstall(): bool
    {
        return true;
    }

    /**
     * enable
     */
    public function enable(): bool
    {
        return true;
    }

    /**
     * disable
     */
    public function disable(): bool
    {
        return true;
    }
}
