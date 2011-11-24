<?php

namespace Wichtel\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WichtelGeneratorCommand extends WichtelCommand
{

	protected function configure()
	{
		parent::configure();
		$this
			 ->setName('generator')
			 ->setDescription('Generate the wichtels.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->readWichtelFile();

		$this->showWichtels($output);

		$dialog = $this->getHelperSet()->get('dialog');
		if (!$dialog->askConfirmation($output, '<question>Do you want to generate the pairs of wichtels? [yes/no]</question> ', false))
		{
			return;
		}
		$wichtel_pairs = $this->getWichtelPairs($output);
		$output->writeln('<info>Success!</info>');
		if (!$dialog->askConfirmation($output, '<question>Do you want to view the wichtel pairs? [yes/no]</question> ', false))
		{
			return;
		}
		foreach ($wichtel_pairs as $pair)
		{
			$output->writeln(sprintf('%s <%s> has to buy something for %s <%s>', $pair[0]['name'], $pair[0]['email'], $pair[1]['name'], $pair[1]['email']));
		}
	}

	protected function getWichtelPairs(OutputInterface $output, $count = 0)
	{
		$wichtels = $this->getWichtels();
		$partners = $wichtels;
		shuffle($partners);

		$pairs = array();
		for ($i=0; $i < count($wichtels); $i++)
		{ 
			$pairs[] = array(
				$wichtels[$i],
				$partners[$i]
			);
		}
		foreach ($pairs as $pair)
		{
			if ($pair[0]['email'] === $pair[1]['email'])
			{
				$output->writeln(sprintf('<error>Try %d failed. Another one...</error>', $count));
				return $this->getWichtelPairs($output, $count + 1);
			}
		}
		return $pairs;
	}

}
