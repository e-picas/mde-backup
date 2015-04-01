<?php
/*
 * This file is part of the PHP-MarkdownExtended package.
 *
 * (c) Pierre Cassat <me@e-piwi.fr> and contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MarkdownExtended\Console;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use \MarkdownExtended\MarkdownExtended;
use \MarkdownExtended\Config;
use \MarkdownExtended\API as MDE_API;
use \MarkdownExtended\Helper as MDE_Helper;
use \MarkdownExtended\Exception as MDE_Exception;

/**
 * Command line controller/interface for MarkdownExtended
 *
 * @package MarkdownExtended\CommandLine
 */
class MDECommand
    extends Command
{

    /**
     * @var     \MarkdownExtended\MarkdownExtended
     */
    protected static $mde_instance;

    protected $mde_input_arg    = '"mde input"';
    protected $cmd_options      = array();
    protected $cmd_args         = array();
    protected $cmd_results      = array();

    protected $output;
    protected $input;

    protected function configure()
    {
        $class_name     = MarkdownExtended::MDE_NAME;
        $class_version  = MarkdownExtended::MDE_VERSION;
        $class_sources  = MarkdownExtended::MDE_SOURCES;
        $this
            ->setName('markdown-extended')
            ->setProcessTitle('markdown-extended-php')
            ->setHelp("
Converts markdown-extended syntax text(s) source(s) from specified file(s) (or STDIN).
The rendering can be the full parsed content or just a part of this content.
By default, result is written through STDOUT in HTML format.

To transform a file content, write its path as script argument. To process a list of input
files, just write file paths as arguments, separated by space.

To transform a string read from STDIN, write it as last argument between double-quotes or EOF.
You can also use the output of a previous command using the pipe notation.

For a full manual, try `man ./path/to/markdown-extended.man` if the file exists ;
if it doesn't, you can try option `--man` of this script to generate it if possible.

More information at <{$class_sources}>.
            ")
            ->addArgument(
                $this->mde_input_arg,
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Markdown string(s) or file(s) path(s) to parse.'
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Configuration file to use (INI format)'
            )
            ->addOption(
                'extract',
                'e',
                InputOption::VALUE_OPTIONAL,
                'Extract some data (the meta data array by default) from the input'
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Format of the output (default is HTML)'
            )
            ->addOption(
                'multi',
                'm',
                InputOption::VALUE_NONE,
                'Multi-files input (automatic if multiple file names found)'
            )
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'Specify a file (or a file mask) to write generated content in'
            )
            ->addOption(
                'template',
                't',
                InputOption::VALUE_OPTIONAL,
                'Load the content in a template file (configuration template by default)'
            )
        ;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        return $this;
    }

    public function setInput(InputInterface $input)
    {
        $this->input = $input;
        return $this;
    }

    protected function verbose()
    {
        if (OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity()) {
            foreach (func_get_args() as $arg) {
                if (is_string($arg)) {
                    $this->output->writeln($arg);
                } else {
                    $this->output->writeln(var_export($arg,true));
                }
            }
        }
    }

    protected function veryVerbose()
    {
        if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $this->output->getVerbosity()) {
            foreach (func_get_args() as $arg) {
                if (is_string($arg)) {
                    $this->output->writeln($arg);
                } else {
                    $this->output->writeln(var_export($arg,true));
                }
            }
        }
    }

    protected function debug()
    {
        if (OutputInterface::VERBOSITY_DEBUG <= $this->output->getVerbosity()) {
            foreach (func_get_args() as $arg) {
                if (is_string($arg)) {
                    $this->output->writeln($arg);
                } else {
                    $this->output->writeln(var_export($arg,true));
                }
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->setInput($input)
            ->setOutput($output)
            ->validateArguments()
            ->validateOptions()
            ->processArguments()
        ;

        // DEBUG
        $this->debug('Input arguments: ', $this->cmd_args);
        $this->debug('Input options: ', $this->cmd_options);
//        $this->debug('MDE configuration: ', MarkdownExtended::get('Config')->getAll());
        $this->debug('Final result: ', $this->cmd_results);

//        $output->writeln($text);
    }

    protected function validateArguments()
    {
        // global input argument(s)
        $this->cmd_args = $this->input->getArgument($this->mde_input_arg);
        // any piped content
        $piped = $this->readSafeStdin();
        if (!empty($piped)) {
            $this->cmd_args[] = $piped;
        }
        return $this;
    }

    protected function validateOptions()
    {
        // global input options
        $this->cmd_options = $this->input->getOptions();

        // multi-input?
        if (!$this->cmd_options['multi'] && count($this->cmd_args)>1) {
            $this->cmd_options['multi'] = true;
        }

        // validate config file path
        if (!is_null($this->cmd_options['config'])) {
            $this->validatePathArgument($this->cmd_options['config'], 'config');
        }

        // validate template file path
        if (!is_null($this->cmd_options['template']) && is_string($this->cmd_options['template'])) {
            $this->validatePathArgument($this->cmd_options['template'], 'template');
        }

        return $this;
    }

    protected function processArguments()
    {
        $parser = $this->getMdeParser();
        foreach ($this->cmd_args as $i=>$input) {
            if (file_exists($input)) {
                $index = $input;
                $md_content = MDE_API::factory('Content', array(null, $input));
            } else {
                $index = 'STDIN#' . ($i+1);
                $md_content = MDE_API::factory('Content', array($input));
            }

            if (!is_null($this->cmd_options['template'])) {
                if (!is_string($this->cmd_options['template'])) {
                    $md_output = $parser->parse($md_content);
                    $this->cmd_results[$index] = $md_output->getFullContent();
                } else {
                    MarkdownExtended::setConfig('template', true, 'templater');
                    MarkdownExtended::setConfig('user_template', $this->cmd_options['template'], 'templater');
                    $md_output = $parser
                        ->parse($md_content)
                        ->getContent();
                    $mde_tpl = MarkdownExtended::getTemplater();
                    $this->cmd_results[$index] = $mde_tpl->parse()->__toString();
                }
            } else {
                $md_output = $parser->parse($md_content);
                $this->cmd_results[$index] = $md_output->getContent()->getBody();
            }

        }
        return $this;
    }

    /**
     * Use of the PHP Markdown Extended class as a singleton
     *
     * @return  \MarkdownExtended\MarkdownExtended
     */
    protected function getMdeParser()
    {
        if (empty(self::$mde_instance)) {
            self::$mde_instance = MarkdownExtended::create();
        }

        $config = array();
        if (!is_null($this->cmd_options['config'])) {
            $config['config_file'] = $this->cmd_options['config'];
        }
        if (!is_null($this->cmd_options['format'])) {
            $config['output_format'] = $this->cmd_options['format'];
        }

        return self::$mde_instance->get('Parser', $config, MDE_API::FAIL_WITH_ERROR);
    }

    protected function validatePathArgument($path, $arg_name)
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                $ok = $this->validatePathArgument($p, $arg_name);
            }
            return $ok;
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(
                sprintf('Value for argument %s must be a valid file path (got "%s")', $arg_name, $path)
            );
        }
        return true;
    }

    /**
     * Get any output from previous command STDIN piped
     * see <http://stackoverflow.com/a/9711142/2512020>
     *
     * @return  string|null
     */
    protected function readSafeStdin()
    {
        $data   = '';
        $stdin  = defined('STDIN') ? STDIN : fopen('php://stdin', 'c+');
        $read   = array($stdin);
        $write  = array();
        $except = array();
        try {
            $result = stream_select($read, $write, $except, 0);
            if ($result !== false && $result > 0) {
                while (!feof($stdin)) {
                    $data .= fgets($stdin);
                }
            }
            @file_put_contents($stdin, '');
        } catch (\Exception $e) {
            $data = null;
        }
        return $data;
    }

}

// Endfile
