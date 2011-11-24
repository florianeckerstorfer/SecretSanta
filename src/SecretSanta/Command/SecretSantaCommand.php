<?php

namespace SecretSanta\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class SecretSantaCommand extends Command
{

	/** @var string */
	private $secretsanta_file;

	/** @var array */
	private $secretSantas = array();
	
	protected function configure()
	{
		$this->secretsanta_file = BASE_DIR . '/data/secret-santas.txt';
	}

	protected function showSecretSantas(OutputInterface $output)
	{
		$dialog = $this->getHelperSet()->get('dialog');
		if ($dialog->askConfirmation($output, '<question>Do you want to view the list of current Secret Santas? [yes/no]</question> ', false))
		{
			foreach ($this->secretSantas as $secretsanta)
			{
				$output->writeln(sprintf('%s <%s>', $secretsanta['name'], $secretsanta['email']));
			}
		}
	}

	protected function readSecretSantaFile()
	{
		$lines = explode("\n", file_get_contents($this->secretsanta_file));
		foreach ($lines as $line)
		{
			if (strlen(trim($line)))
			{
				list($name, $email) = explode(';;;', trim($line));
				$this->addSecretSanta($name, $email);
			}
		}
	}

	protected function writeSecretSantaFile()
	{
		$lines = array();
		foreach ($this->secretSantas as $secretsanta)
		{
			$lines[] = sprintf('%s;;;%s', $secretsanta['name'], $secretsanta['email']);
		}
		file_put_contents($this->secretsanta_file, implode("\n", $lines) . "\n");
	}

	protected function addSecretSanta($name, $email)
	{
		if (strlen($name) > 0 && strlen($email) > 0)
		{
			$this->secretSantas[] = array(
				'name'		=> $name,
				'email'		=> $email,
			);
			return true;
		}
		return false;
	}

	protected function getSecretSantas()
	{
		return $this->secretSantas;
	}

	protected function getSecretSantaFile()
	{
		return $this->secretsanta_file;
	}

}
