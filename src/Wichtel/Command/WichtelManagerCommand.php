<?php

namespace Wichtel\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WichtelManagerCommand extends WichtelCommand
{
	
	protected function configure()
	{
		parent::configure();
		$this
			 ->setName('manager')
			 ->setDescription('Manage the Wichtels.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->readWichtelFile();

		$dialog = $this->getHelperSet()->get('dialog');

		$this->showWichtels($output);
		while ($dialog->askConfirmation($output, '<question>Do you want to add another wichtel? [yes/no]</question> ', false))
		{
			$name = $dialog->ask($output, 'Please enter the name of the wichtel: ', '');
			$email = $dialog->ask($output, 'Please enter the email address of the wichtel: ', '');
			if ($this->addWichtel($name, $email))
			{
				$output->writeln(sprintf('<info>Added Wichtel %s <%s></info>', $name, $email));
			}
			$this->showWichtels($output);
		}
		$this->writeWichtelFile();
		$output->writeln(sprintf('<info>Saved wichtels to "%s".</info>', $this->getWichtelFile()));
	}

}
