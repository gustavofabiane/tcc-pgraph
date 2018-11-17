<?php

namespace Pgraph\Command;

use Pgraph\Container\ContainerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
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
     * @return string|null The default command name or null when no default name is set
     */
    public static final function getDefaultName()
    {
        $class = get_called_class();
        $r = new \ReflectionProperty($class, 'defaultName');

        $defaultName = $class === $r->class ? static::$defaultName : null;

        if (!$defaultName) {
            $properties = (new \ReflectionClass($class))->getDefaultProperties();
            if (array_key_exists('name', $properties) && !empty($properties['name'])) {
                $defaultName = $properties['name'];
            }
        }

        if (!$defaultName) {

            $classFullName = explode('\\', $class);
            $className = end($classFullName);
            $commandName = preg_replace('/Command$/', '', $className);

            if (!empty($commandName)) {
                $parts = preg_split('/(?=[A-Z])/', lcfirst($commandName));
                $defaultName = strtolower(implode('-', $parts));
            }
        }

        return $defaultName;
    }

    /**
     * Create a new command instance with the given name if provided.
     *
     * @param string|null $name
     */
    public function __construct(?string $name = null)
    {
        if (null !== $name || null !== $name = static::getDefaultName()) {
            $this->setName($name);
        }

        parent::__construct($this->getName());
    }

    /**
     * Get an specific argument from input data.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function arg(string $name, $default = null)
    {
        if (!$this->input->hasArgument($name)) {
            return $default;
        }
        return $this->input->getArgument($name);
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
        if (!$this->input->hasOption($name)) {
            return $default;
        }
        return $this->input->getOption($name);
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
     * Write the message with the given style.
     *
     * @param string $message
     * @param string $style
     * @param bool $newLine
     * @return void
     */
    protected function styled(string $message, string $style, bool $newLine = true): void
    {
        $this->write(sprintf('<%s>%s</%s>', $style, $message, $style), $newLine);
    }

    /**
     * Write the given message with info style.
     *
     * @param string $message
     * @param bool $newLine
     * @return void
     */
    protected function info(string $message, bool $newLine = true): void
    {
        $this->styled($message, 'info', $newLine);
    }

    /**
     * Write the given message with comment style.
     *
     * @param string $message
     * @param bool $newLine
     * @return void
     */
    protected function comment(string $message, bool $newLine = true): void
    {
        $this->styled($message, 'comment', $newLine);
    }

    /**
     * Write the given message with error style.
     *
     * @param string $message
     * @param bool $newLine
     * @return void
     */
    protected function error(string $message, bool $newLine = true): void
    {
        $this->styled($message, 'error', $newLine);
    }

    /**
     * Provide a progress bar instance with the given total number of units given.
     *
     * @param int $total
     * @return ProgressBar
     */
    protected function progressBar(int $total, string $format = null): ProgressBar
    {
        $progressBar = new ProgressBar($this->output, $total);
        if ($format) {
            $progressBar->setFormat($format);
        }
        return $progressBar;
    }

    /**
     * Provide a built simple question for the given parameters.
     *
     * @param string $question
     * @param mixed $defaultAwnser
     * @return Question
     */
    protected function question(string $question, $defaultAwnser = null): Question
    {
        return new Question($question, $defaultAwnser);
    }

    /**
     * Provide a built confirmation question for the given parameters.
     *
     * @param string $question
     * @param bool $defaultAwnser
     * @param string $confirmPattern
     * @return ConfirmationQuestion
     */
    protected function confirmQuestion(
        string $question,
        bool $defaultAwnser = false,
        string $confirmPattern = '/^y/i'
    ): ConfirmationQuestion {
        return new ConfirmationQuestion(
            $question, $defaultAwnser, $confirmPattern
        );
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
    protected function choiceQuestion(
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
     * Ask user with the given question instance using the command helper.
     *
     * @param Question $question
     * @param bool $hiddenInput
     * @param array $autoCompleterValues
     * @return mixed
     */
    protected final function askWith(
        Question $question, 
        bool $hiddenInput = false, 
        array $autoCompleterValues = null
    ) {
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
     * @return mixed
     */
    protected function ask(string $question, string $defaultAwnser = null, bool $hiddenInput = false)
    {
        return $this->askWith($this->question($question, $defaultAwnser), $hiddenInput);
    }

    /**
     * Ask user for a secret value that will not be displayed on screen.
     *
     * @param string $question
     * @param string $defaultAwnser
     * @return mixed
     */
    protected function secret(string $question, string $defaultAwnser = null)
    {
        return $this->ask($question, $defaultAwnser, true);
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
        return $this->askWith($this->confirmQuestion(
            $question, $defaultAwnser, $confirmPattern
        ));
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
     * Call a console command.
     *
     * @param string $command
     * @param array $arguments
     * @param bool $interactive
     * @param bool $silent
     * @return int
     */
    protected function call(
        string $command, 
        array $arguments = [], 
        bool $interactive = true, 
        bool $silent = false
    ): int {
        $arguments['command'] = $command;

        $input = new ArrayInput($arguments);
        $input->setInteractive(
            $interactive || !array_key_exists('--no-interaction', $arguments)
        );

        return $this->getApplication()->find($command)->run(
            $input, $silent ? new NullOutput() : $this->output
        );
    }

    /**
     * Call a console command with silent output.
     *
     * @param string $command
     * @param array $arguments
     * @param bool $interactive
     * @return int
     */
    protected function silent(string $command, array $arguments = [], bool $interactive = true): int 
    {
        return $this->call($command, $arguments, $interactive, true);
    }

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
