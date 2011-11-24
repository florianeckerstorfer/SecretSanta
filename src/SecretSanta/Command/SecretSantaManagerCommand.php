<?php

namespace SecretSanta\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package    at.theroadtojoy.secretsanta
 * @subpackage Command
 * @author     Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 */
class SecretSantaManagerCommand extends SecretSantaCommand
{
	
	protected function configure()
	{
		parent::configure();
		$this
			 ->setName('manager')
			 ->setDescription('Manage the SecretSantas.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->readSecretSantaFile();

		$dialog = $this->getHelperSet()->get('dialog');

		$this->showSecretSantas($output);

		if ($dialog->askConfirmation($output, '<question>Do you want to delete all current Secret Santas? [yes/no]</question> ', false))
		{
			if ($dialog->askConfirmation($output, '<error>Do you really want to delete all current Secret Santas? [yes/no]</error> ', false))
			{
				$this->clearSecretSantas();
				$output->writeln('<info>Deleted all current Secret Santas.</info>');
			}
		}

		while ($dialog->askConfirmation($output, '<question>Do you want to add another Secret Santa? [yes/no]</question> ', false))
		{
			$name = $dialog->ask($output, 'Please enter the name of the Secret Santa: ', '');
			$email = $dialog->ask($output, 'Please enter the email address of the Secret Santa: ', '');
			if ($this->addSecretSanta($name, $email))
			{
				$output->writeln(sprintf('<info>Added SecretSanta %s <%s></info>', $name, $email));
			}
			$this->showSecretSantas($output);
		}
		$this->writeSecretSantaFile();
		$output->writeln(sprintf('<info>Saved Secret Santas to "%s".</info>', $this->getSecretSantaFile()));
	}

}
