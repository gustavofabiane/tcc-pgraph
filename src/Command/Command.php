<?php

namespace Framework\Command;

use Framework\Container\ContainerInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

abstract class Command extends SymfonyCommand
{
    /**
     * Container implementation instance.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The command input handler.
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * The command output handler.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Values for user input auto completion.
     *
     * @var array
     */
    private $autoCompleterValues;

    /**
     * Get an specific argument from input data.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function arg(string $name, $default = null)
    {
        return $this->input->getArgument($name) ?: $default;
    }

    /**
     * Get the set of present command arguments.
     *
     * @return array
     */
    protected function args(): array
    {
        return $this->input->getArguments();
    }

    /**
     * Get an specific option from input data.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function opt(string $name, $default = null)
    {
        return $this->input->getOption($name) ?: $default;
    }

    /**
     * Get the set of present command options.
     *
     * @return array
     */
    protected function opts(): array
    {
        return $this->input->getOptions();
    }

    /**
     * Write the given message(s) to the output.
     *
     * @param array|string $messages
     * @param boolean $newLine
     * @param array $options
     * @return void
     */
    protected function write($messages, bool $newLine = false, array $options = []): void
    {
        $this->output->write($messages, $newLine, $options);
    }

    /**
     * Write the given message(s) with a line break after each one.
     *
     * @param array|string $messages
     * @param array $options
     * @return void
     */
    protected function writeln($messages, array $options = []): void
    {
        $this->write($messages, true, $options);
    }

    /**
     * Produces a line break in output state.
     *
     * @return void
     */
    protected function breakLine(): void
    {
        $this->write('', true);
    }

    /**
     * Return the command question helper instance.
     *
     * @return QuestionHelper
     */
    protected final function askWith(
        Question $question, 
        bool $hiddenInput = false, 
        array $autoCompleterValues = null
    ): QuestionHelper{
        if ($autoCompleterValues) {
            $question->setAutocompleterValues($autoCompleterValues);
        } elseif ($this->autoCompleterValues) {
            $question->setAutocompleterValues($this->autoCompleterValues);
            $this->cleanAutoCompleteValues();
        }
        if ($hiddenInput) {
            $question->setHidden(true)->setHiddenFallback(false);
        }
        return $this->getHelper('question')->ask($this->input, $this->output, $question);
    }

    /**
     * Ask user for value based on a custom question and arguments.
     *
     * @param string $question
     * @return float|int|string
     */
    protected function ask(string $question, string $defaultAwnser = 'n', bool $hiddenInput = false)
    {
        return $this->askWith(new Question($question, $defaultAwnser), $hiddenInput);
    }

    /**
     * Ask user for confirmation based on the given question.
     *
     * @param string $question
     * @param bool $defaultAwnser
     * @param string $confirmPattern
     * @return bool
     */
    protected function confirm(
        string $question,
        bool $defaultAwnser = false,
        string $confirmPattern = '/^y/i'
    ): bool {
        return $this->askWith(new ConfirmationQuestion(
            $question, $defaultAwnser, $confirmPattern
        ));
    }

    /**
     * Provide a built choice question for the given parameters.
     *
     * @param string $question
     * @param array $options
     * @param int|null $defaultChoice
     * @param string|null $errorMessage
     * @param bool $multipleChoice
     * @return ChoiceQuestion
     */
    private function choiceQuestion(
        string $question,
        array $options,
        ?int $defaultChoice = null,
        ?string $errorMessage = null,
        bool $multipleChoice = false
    ): ChoiceQuestion {
        $choiceQuestion = new ChoiceQuestion($question, $options, $defaultChoice);
        $choiceQuestion->setMultiselect($multipleChoice);
        if ($errorMessage) {
            $choice->setErrorMessage($errorMessage);
        }
        return $choiceQuestion;
    }

    /**
     * Ask user to choose from a predefined list of values.
     *
     * @param string $question
     * @param array $options
     * @param int $defaultChoice
     * @param string $errorMessage
     * @param bool $multipleChoice
     * @return mixed
     */
    protected function choose(
        string $question,
        array $options,
        int $defaultChoice = null,
        string $errorMessage = null,
        bool $multipleChoice = false
    ) {
        return $this->askWith($this->choiceQuestion(
            $question, $options, $defaultChoice, $errorMessage, $multipleChoice
        ));
    }

    /**
     * Ask user to choose from predefined list of values, but allows more than one choice.
     *
     * @param string $question
     * @param array $options
     * @param string $defaultChoices A string with the default choices keys, separeted by comma
     * @param string $errorMessage
     * @return array
     */
    protected function chooseMany(
        string $question,
        array $options,
        string $defaultChoices = null,
        string $errorMessage = null
    ): array {
        return $this->askWith($this->choiceQuestion(
            $question, $options, $defaultChoices, $errorMessage, true
        ));
    }

    /**
     * Run the command setting up input and output before
     * delegate the process to the parent::run() method.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        return parent::run($input, $output);
    }

    /**
     * Set the input and output, execute the handle() method
     * and updates the output at the end of the execution.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        return $this->container->resolve([$this, 'main'], [
            'input'  => $input,
            'output' => $output
        ]) ?: null;
    }

    /**
     * Handle the command execution logic with input and output abstraction.
     *
     * @return int|null
     */
    abstract public function main();

    /**
     * Set global auto completion values to be used 
     * in the next called command question.
     *
     * @param array $values
     * @return void
     */
    protected function setAutoCompleterValues(array $values): void
    {
        $this->autoCompleterValues = $values;
    }

    /**
     * Clean input auto complete values defined in command.
     *
     * @return void
     */
    protected function cleanAutoCompleteValues(): void
    {
        $this->autoCompleterValues = null;
    }

    /**
     * Set console container instance.
     *
     * @param ContainerInterface $container
     * @return static
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Get console container instance.
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
