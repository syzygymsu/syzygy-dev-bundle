<?php

namespace Syzygy\DevBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EntitiesFixCommand extends AbstractEntitiesCommand {
	protected function configure() {
		$this
			->setName('syzygy:entities:fix')
				->setDescription( <<<EOT
Changes `private` modifier to `protected` in entities.
ATTENTION: modifier SHOULD be first literal on the line (excluding spaces and tabs).
WARNING: Word `private` in strings, comments, constants and anything else WILL BE REPLACED AS WELL (if satisfies previous condition).
EOT
		)
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
				$output->writeln(sprintf('Fixing entity "<info>%s</info>"', $name));
				$metadata = $manager->getClassMetadata($name, $input->getOption('path'));
			} else {
				$output->writeln(sprintf('Fixing entities for namespace "<info>%s</info>"', $name));
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

			$output->writeln(sprintf('  > fixing <comment>%s</comment>', $m->name));

			$res = $this->fixEntity($m, $entityMetadata->getPath());

			if(!$res) {
				$output->writeln(sprintf('> FAILED'));
			}
		}
	}

	public function fixEntity(ClassMetadata $metadata, $path) {
		$file_path = $path. '/'. str_replace('\\', DIRECTORY_SEPARATOR, $metadata->getName()) . '.php';

		$replacements = $this->getFixes();

		if(!file_exists($file_path)) {
			throw new \Exception(sprintf('Failed to locate file for %s', $metadata->getName()));
		}

		$delimiter = $this->getDelimiter();
		$lines = file($file_path);
		$afterDelimiter = false;
		$lines = array_map(function($line) use($replacements, $delimiter, &$afterDelimiter) {
			if($afterDelimiter) {
				foreach($replacements as $replacement) {
					$line = preg_replace($replacement[0], $replacement[1], $line);
				}
			} else {
				if(false !== strpos($line, $delimiter)) {
					$afterDelimiter = true;
				}
			}
			return $line;
		}, $lines);
		file_put_contents($file_path, implode($lines));

		return true;
	}
}
