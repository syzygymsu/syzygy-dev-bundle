<?php

namespace Syzygy\DevBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class AbstractEntitiesCommand extends ContainerAwareCommand {

	/**
	 * @return \Symfony\Bundle\FrameworkBundle\Console\Application
	 */
	public function getApplication() {
		return parent::getApplication();
	}

	public function getDelimiter() {
		return $this->getContainer()->getParameter('syzygy.entities.delimiter');
	}

	public function getEnding() {
		return $this->getContainer()->getParameter('syzygy.entities.ending');
	}

	public function getFixes() {
		return $this->getContainer()->getParameter('syzygy.entities.fixes');
	}

}
