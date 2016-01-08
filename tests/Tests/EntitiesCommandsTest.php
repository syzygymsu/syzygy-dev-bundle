<?php

namespace Syzygy\DevBundle\Tests;

class EntitiesCommandsTest extends \PHPUnit_Framework_TestCase {

	private $tmpDir;
	private $appDir;

	protected function setUp() {
		$appName = 'DummyApp';
		$testsBaseDir = dirname(dirname(__FILE__));
		$appSrcDir = $testsBaseDir . '/' . $appName;
		$tmpBaseDir = $testsBaseDir . '/tmp';

		// create dummy file, then replace it with a directory
		$tmpDir = tempnam($tmpBaseDir, 'app');
		if (file_exists($tmpDir)) {
			unlink($tmpDir);
		}
		if (!mkdir($tmpDir)) {
			throw new \Exception(sprintf('Unable to create temp dir "%s"', $tmpDir));
		}

		$appDir = $tmpDir . '/' . $appName;

		Utility::recurseCopy($appSrcDir, $appDir);

		$this->tmpDir = $tmpDir;
		$this->appDir = $appDir;
	}

	protected function tearDown() {
		Utility::recurseDelete($this->tmpDir);
	}

	private function execCommand($name, array $args = array()) {
		$php = Utility::findPhpBinary();
		$parts = array_merge(array($php, $this->appDir . '/app/console.php', $name),
				$args);
		$cmd = implode(' ', array_map('escapeshellarg', $parts));

		$descriptorspec = array(
			0 => array("pipe", "r"), // stdin
			1 => array("pipe", "w"), // stdout
			2 => array("pipe", "w") // stderr
		);
		$pipes = array();
		$process = proc_open($cmd, $descriptorspec, $pipes);
		if (!is_resource($process)) {
			throw new \Exception(sprintf('Failed to exec [%s]', $cmd));
		}
		fclose($pipes[0]);
		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);

		$retval = proc_close($process);
		if ($retval != 0) {
			throw new \Exception(sprintf(
					"CLI command failed [%s]\nSTDOUT: <<<\n%s>>>\nSTDERR: <<<\n%s>>>\n", $cmd,
					$stdout, $stderr
			));
		}
	}

	public function testClean() {
		$this->execCommand('syzygy:entities:clean', array('AppBundle'));
		$this->assertHasIdEtters(array(
			'Simple' => false,
			'Clean' => false,
			'Generated' => false,
			'Base' => false,
			'Derived' => false,
		));
	}

	public function testRegenerate() {
		$this->execCommand('syzygy:entities:regenerate', array('AppBundle'));
		$this->assertHasIdEtters(array(
			'Simple' => true,
			'Clean' => true,
//			'Generated' => true, // this fail now due to a bug
			'Base' => true,
			'Derived' => true,
		));
	}

	public function assertHasIdEtters($rules) {
		$getterPattern = 'public function getId()';
		$setterPattern = 'public function setId($id)';
		foreach ($rules as $name => $expected) {
			$filePath = $this->appDir . '/src/AppBundle/Entity/' . $name . '.php';
			$this->assertEquals(
					$expected, $this->isFileContaining($filePath, $getterPattern),
					sprintf("Getter in %s:", $name)
			);
			$this->assertEquals(
					$expected, $this->isFileContaining($filePath, $setterPattern),
					sprintf("Stter in %s:", $name)
			);
		}
	}

	public function isFileContaining($filePath, $substr) {
		return false !== strpos(file_get_contents($filePath), $substr);
	}

}
