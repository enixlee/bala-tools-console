<?php

namespace ZeusConsole\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator;

class GreetCommand extends CommandBase {
	protected function configure() {
		$this->setName ( 'demo:greet' )
			->setDescription ( 'Greet someone' )
			->addArgument ( 'name', InputArgument::OPTIONAL, 'Who do you want to greet?中文' )
			->addOption ( 'yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters' );
	}
	protected function execute(InputInterface $input, OutputInterface $output) {
		$name = $input->getArgument ( 'name' );
		if ($name) {
			$text = 'Hello ' . $name;
		} else {
			$text = 'Hello';
		}

		if ($input->getOption ( 'yell' )) {
			$text = strtoupper ( $text );
		}

		$output->writeln ( $text );

		// green text
		$output->writeln ( '<info>foo</info>' );

		// yellow text
		$output->writeln ( '<comment>foo</comment>' );

		// black text on a cyan background
		$output->writeln ( '<question>foo</question>' );

		// white text on a red background
		$output->writeln ( '<error>foo</error>' );

		$validator = Validation::createValidator ();
		$validatorouts = $validator->validate ( '', [
				new NotBlank (),
				new Type ( [
						'type' => 'string'
				] ),
				new Length ( [
						'min' => 2,
						'max' => 50
				] )
		] );
		for($i = 0; $i < $validatorouts->count (); $i ++) {
			$validatorout = $validatorouts->get ( $i );
			$output->writeln ( '<error>' . $validatorout->getMessage () . '</error>' );
		}

		// echo var_dump ( $validatorout );
	}
}