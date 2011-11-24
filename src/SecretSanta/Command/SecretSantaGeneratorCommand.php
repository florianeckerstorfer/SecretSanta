<?php

namespace SecretSanta\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SecretSantaGeneratorCommand extends SecretSantaCommand
{

	protected function configure()
	{
		parent::configure();
		$this
			 ->setName('generator')
			 ->setDescription('Generate the Secret Santas.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->readSecretSantaFile();

		$this->showSecretSantas($output);

		$dialog = $this->getHelperSet()->get('dialog');
		if (!$dialog->askConfirmation($output, '<question>Do you want to generate the pairs of Secret Santas? [yes/no]</question> ', false))
		{
			return;
		}
		$secretsanta_pairs = $this->getSecretSantaPairs($output);
		$output->writeln('<info>Success!</info>');
		if (!$dialog->askConfirmation($output, '<question>Do you want to view the Secret Santa pairs? [yes/no]</question> ', false))
		{
			return;
		}
		foreach ($secretsanta_pairs as $pair)
		{
			$output->writeln(sprintf('%s <%s> has to buy something for %s <%s>', $pair[0]['name'], $pair[0]['email'], $pair[1]['name'], $pair[1]['email']));
		}
	}

	protected function getSecretSantaPairs(OutputInterface $output, $count = 0)
	{
		$secretsantas = $this->getSecretSantas();
		$partners = $secretsantas;
		shuffle($partners);

		$pairs = array();
		for ($i=0; $i < count($secretsantas); $i++)
		{ 
			$pairs[] = array(
				$secretsantas[$i],
				$partners[$i]
			);
		}
		foreach ($pairs as $pair)
		{
			if ($pair[0]['email'] === $pair[1]['email'])
			{
				$output->writeln(sprintf('<error>Try %d failed. Another one...</error>', $count));
				return $this->getSecretSantaPairs($output, $count + 1);
			}
		}
		return $pairs;
	}

}
