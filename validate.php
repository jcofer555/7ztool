<?php
$path = $_GET['path'] ?? '';
$path = realpath($path);
echo ($path && file_exists($path)) ? '1' : '0';
