<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel {

	public function registerBundles() {
		$bundles = array(
			new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
			new Symfony\Bundle\MonologBundle\MonologBundle(),
			new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
			new Syzygy\DevBundle\SyzygyDevBundle(),
			new AppBundle\AppBundle(),
		);

		return $bundles;
	}

	public function registerContainerConfiguration(LoaderInterface $loader) {
		$loader->load($this->getRootDir() . '/config.yml');
	}

}
