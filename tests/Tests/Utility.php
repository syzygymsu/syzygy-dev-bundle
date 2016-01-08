<?php

namespace Syzygy\DevBundle\Tests;

abstract class Utility {

	static function recurseCopy($src, $dst) {
		@mkdir($dst);
		foreach (scandir($src) as $object) {
			if ($object == '.' || $object == '..') {
				continue;
			}
			$srcPath = $src . '/' . $object;
			$dstPath = $dst . '/' . $object;
			if (is_dir($srcPath)) {
				self::recurseCopy($srcPath, $dstPath);
			} else {
				copy($srcPath, $dstPath);
			}
		}
	}

	static function recurseDelete($dir) {
		foreach (scandir($dir) as $object) {
			if ($object == '.' || $object == '..') {
				continue;
			}
			$path = $dir . '/' . $object;
			if (is_dir($path)) {
				self::recurseDelete($path);
			} else {
				unlink($path);
			}
		}
		rmdir($dir);
	}

	static function findPhpBinary() {
		switch (true) {
			case defined('PHP_BINARY'):
				return PHP_BINARY;
			case is_executable(PHP_BINDIR . '/php'):
				return PHP_BINDIR . '/php';
			default:
				return 'php';
		}
	}

}
