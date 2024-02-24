<?php

declare(strict_types=1);

namespace App\ApiResource\Common;

trait ClassInfoTrait
{
    /**
     * Get class name of the given object.
     */
    private function getObjectClass(object $object): string
    {
        return $this->getRealClassName($object::class);
    }

    /**
     * Get the real class name of a class name that could be a proxy.
     */
    private function getRealClassName(string $className): string
    {
        $positionCg = strrpos($className, '\\__CG__\\');
        $positionPm = strrpos($className, '\\__PM__\\');

        if (false === $positionCg && false === $positionPm) {
            return $className;
        }

        if (false !== $positionCg) {
            return substr($className, $positionCg + 8);
        }

        $className = ltrim($className, '\\');

        return substr(
            $className,
            8 + $positionPm,
            strrpos($className, '\\') - ($positionPm + 8)
        );
    }
}
