<?php

namespace Syzygy\DevBundle\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EntitiesRegenerateCommand extends AbstractEntitiesCommand {
	protected function configure() {
		$this
			->setName('syzygy:entities:regenerate')
			->setDescription('Clears old auto-generated content in entities and generates new')
			->addArgument('name', InputArgument::REQUIRED, 'A bundle name, a namespace, or a class name')
			->addOption('path', null, InputOption::VALUE_REQUIRED, 'The path where to generate entities when it cannot be guessed')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		// CLEAN
		$command = $this->getApplication()->find('syzygy:entities:clean');
		$arguments = array(
			'command' => 'syzygy:entities:clean',
			'name' => $input->getArgument('name'),
		);
		if($input->getOption('path')) {
			$arguments['--path'] = $input->getOption('path');
		}
		$localInput = new ArrayInput($arguments);
		$command->run($localInput, $output);

		// GENERATE
		$command = $this->getApplication()->find('doctrine:generate:entities');
		$arguments = array(
			'command' => 'doctrine:entities:generate',
			'name' => $input->getArgument('name'),
		);
		if($input->getOption('path')) {
			$arguments['--path'] = $input->getOption('path');
		}
		$localInput = new ArrayInput($arguments);
		$command->run($localInput, $output);

		// FIX
		$command = $this->getApplication()->find('syzygy:entities:fix');
		$arguments = array(
			'command' => 'syzygy:entities:fix',
			'name' => $input->getArgument('name'),
		);
		if($input->getOption('path')) {
			$arguments['--path'] = $input->getOption('path');
		}
		$localInput = new ArrayInput($arguments);
		$command->run($localInput, $output);
	}

}
