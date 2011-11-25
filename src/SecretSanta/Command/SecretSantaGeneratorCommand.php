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
		if ($dialog->askConfirmation($output, '<question>Do you want to view the Secret Santa pairs? [yes/no]</question> ', false))
		{
			foreach ($secretsanta_pairs as $pair)
			{
				$output->writeln(sprintf('%s <%s> is the SecretSanta of %s <%s>', $pair[0]['name'], $pair[0]['email'], $pair[1]['name'], $pair[1]['email']));
			}
		}
		if ($dialog->askConfirmation($output, '<question>Do you want to send the Secret Santas via Mail? [yes/no]</question> ', false))
		{
			$subject = 'Secret Santa Notification';
			if ($dialog->askConfirmation($output, '<question>Do you want to enter a custom subject? [yes/no]</question> ', false))
			{
				$subject = $dialog->ask($output, 'Please enter the subject: ');
			}
			$message = "Hello {name}!\n\nYou are the Secret Santa of {p_name} <{p_email}>.\n\nGreetings";
			if ($dialog->askConfirmation($output, '<question>Do you want to enter a custom message? [yes/no]</question> ', false))
			{
				$output->writeln('You can insert the following variables in the text:');
				$output->writeln('<comment>{name}</comment>    Name of the Secret Santa.');
				$output->writeln('<comment>{email}</comment>   Email address of the Secret Santa.');
				$output->writeln('<comment>{p_name}</comment>  Name of the presentee.');
				$output->writeln('<comment>{p_email}</comment> E-Mail of the presentee.');
				$output->writeln('Press <comment>Enter</comment> to save the message. You can insert new lines with <comment>\n</comment>.');
				$message = $dialog->ask($output, "Please enter the message:\n");
			}
			$this->sendSecretSantaMails($output, $secretsanta_pairs, $subject, $message);
		}
	}

	protected function getSecretSantaPairs(OutputInterface $output, $count = 1)
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

	protected function sendSecretSantaMails(OutputInterface $output, array $pairs, $subject, $message_template)
	{
		$mail_failed = false;
		foreach ($pairs as $pair)
		{
			$message = str_replace(array(
				'{name}', '{email}', '{p_name}', '{p_email}'
			), array(
				$pair[0]['name'],
				$pair[0]['email'],
				$pair[1]['name'],
				$pair[1]['email']
			), $message_template);
			if ($this->sendMail($pair[0]['name'], $pair[0]['email'], $subject, $message))
			{
				$output->writeln(sprintf('<info>Sent email to %s <%s>.</info>', $pair[0]['name'], $pair[0]['email']));
			}
			else
			{
				$output->writeln(sprintf('<error>Failed to send mail to %s <%s>.</error>', $pair[0]['name'], $pair[0]['email']));
			}
		}
	}

	protected function sendMail($name, $email, $subject, $body)
	{
		$transport = \Swift_SmtpTransport::newInstance(SMTP_SERVER, 25)
			->setUsername(SMTP_USERNAME)
			->setPassword(SMTP_PASSWORD)
		;
		$mailer = \Swift_Mailer::newInstance($transport);

		$message = \Swift_Message::newInstance()
			->setSubject($subject)
			->setFrom(array(SENDER_ADDRESS => SENDER_NAME))
			->setTo(array($email => $name))
			->setBody($body)
		;
		return $mailer->send($message);
	}

}
