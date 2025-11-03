<?php

if (file_exists(dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php')) {
    require dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php';
} elseif (file_exists(dirname(__DIR__).'/var/cache/dev/App_KernelDevDebugContainer.preload.php')) {
    require dirname(__DIR__).'/var/cache/dev/App_KernelDevDebugContainer.preload.php';
}
