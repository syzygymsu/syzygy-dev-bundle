<?php

namespace Syzygy\DevBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EntitiesCleanCommand extends AbstractEntitiesCommand {
	protected function configure() {
		$this
			->setName('syzygy:entities:clean')
				->setDescription('Clears old auto-generated content in entities')
			->addArgument('name', InputArgument::REQUIRED, 'A bundle name, a namespace, or a class name')
			->addOption('path', null, InputOption::VALUE_REQUIRED, 'The path where to generate entities when it cannot be guessed')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$manager = new DisconnectedMetadataFactory($this->getContainer()->get('doctrine'));

		try {
			$bundle = $this->getApplication()->getKernel()->getBundle($input->getArgument('name'));

			$output->writeln(sprintf('Clearing entities for bundle "<info>%s</info>"', $bundle->getName()));
			$metadata = $manager->getBundleMetadata($bundle);
		} catch (\InvalidArgumentException $e) {
			$name = strtr($input->getArgument('name'), '/', '\\');

			if (false !== $pos = strpos($name, ':')) {
				$name = $this->getContainer()->get('doctrine')->getAliasNamespace(substr($name, 0, $pos)).'\\'.substr($name, $pos + 1);
			}

			if (class_exists($name)) {
				$output->writeln(sprintf('Clearing entity "<info>%s</info>"', $name));
				$metadata = $manager->getClassMetadata($name, $input->getOption('path'));
			} else {
				$output->writeln(sprintf('Clearing entities for namespace "<info>%s</info>"', $name));
				$metadata = $manager->getNamespaceMetadata($name, $input->getOption('path'));
			}
		}

		foreach ($metadata->getMetadata() as $m) {
			// Getting the metadata for the entity class once more to get the correct path if the namespace has multiple occurrences
			try {
				$entityMetadata = $manager->getClassMetadata($m->getName(), $input->getOption('path'));
			} catch (\RuntimeException $e) {
				// fall back to the bundle metadata when no entity class could be found
				$entityMetadata = $metadata;
			}

			$output->writeln(sprintf('  > clearing <comment>%s</comment>', $m->name));

			$res = $this->clearEntity($m, $entityMetadata->getPath());

			if(!$res) {
				$output->writeln(sprintf('> FAILED'));
			}
		}
	}

	public function clearEntity(ClassMetadata $metadata, $path) {
		$file_path = $path. '/'. str_replace('\\', DIRECTORY_SEPARATOR, $metadata->getName()) . '.php';
		if(!file_exists($file_path)) {
			throw new \Exception(sprintf('Failed to locate file for %s', $metadata->getName()));
		}

		$hf = fopen($file_path, 'c+');

		while(true) {
			$s = fgets($hf);
			if(false === $s) {
				// Target delimiter not found in file
				fclose($hf);
				return false;
			}

			if(false !== strpos($s, $this->getDelimiter())) {
				break;
			}
		}

		fwrite($hf, $this->getEnding());
		ftruncate($hf, ftell($hf));

		return true;
	}
}
