<?php

namespace Wichtel\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class WichtelCommand extends Command
{

	/** @var string */
	private $wichtel_file;

	/** @var array */
	private $wichtels = array();
	
	protected function configure()
	{
		$this->wichtel_file = BASE_DIR . '/data/wichtels.txt';
	}

	protected function showWichtels(OutputInterface $output)
	{
		$dialog = $this->getHelperSet()->get('dialog');
		if ($dialog->askConfirmation($output, '<question>Do you want to view the list of current wichtels? [yes/no]</question> ', false))
		{
			foreach ($this->wichtels as $wichtel)
			{
				$output->writeln(sprintf('%s <%s>', $wichtel['name'], $wichtel['email']));
			}
		}
	}

	protected function readWichtelFile()
	{
		$lines = explode("\n", file_get_contents($this->wichtel_file));
		foreach ($lines as $line)
		{
			if (strlen(trim($line)))
			{
				list($name, $email) = explode(';;;', trim($line));
				$this->addWichtel($name, $email);
			}
		}
	}

	protected function writeWichtelFile()
	{
		$lines = array();
		foreach ($this->wichtels as $wichtel)
		{
			$lines[] = sprintf('%s;;;%s', $wichtel['name'], $wichtel['email']);
		}
		file_put_contents($this->wichtel_file, implode("\n", $lines) . "\n");
	}

	protected function addWichtel($name, $email)
	{
		if (strlen($name) > 0 && strlen($email) > 0)
		{
			$this->wichtels[] = array(
				'name'		=> $name,
				'email'		=> $email,
			);
			return true;
		}
		return false;
	}

	protected function getWichtels()
	{
		return $this->wichtels;
	}

	protected function getWichtelFile()
	{
		return $this->wichtel_file;
	}

}
