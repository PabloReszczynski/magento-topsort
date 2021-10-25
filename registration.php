<?php
/**
 * @copyright Topsort
 * @author Kyrylo Kostiukov
 */
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Topsort_Integration',
    isset($file) ? (realpath(dirname(dirname($file))) . DIRECTORY_SEPARATOR . basename(dirname($file))) : __DIR__
);