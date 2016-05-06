<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace KryuuModuleCreator\Prompt;

use Zend\Console\Prompt\Char;
use Zend\Console\Exception;

class ExSelect extends Char
{ 
    protected $xPos = null;
    
    protected $yPos = null;
    
    protected $bgColor = null;
    
    protected $fgColor = null;
    
    /**
     * @var string
     */
    protected $promptText = 'Please select an option';

    /**
     * @var bool
     */
    protected $ignoreCase = true;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Ask the user to select one of pre-defined options
     *
     * @param string    $promptText     The prompt text to display in console
     * @param array     $options        Allowed options
     * @param bool      $allowEmpty     Allow empty (no) selection?
     * @param bool      $echo           True to display selected option?
     * @throws Exception\BadMethodCallException if no options available
     */
    public function __construct(
        $promptText = 'Please select one option',
        $options = [],
        $allowEmpty = false,
        $echo = false
    ) {
        if ($promptText !== null) {
            $this->setPromptText($promptText);
        }

        if (!count($options)) {
            throw new Exception\BadMethodCallException(
                'Cannot construct a "select" prompt without any options'
            );
        }

        $this->setOptions($options);

        if ($allowEmpty !== null) {
            $this->setAllowEmpty($allowEmpty);
        }

        if ($echo !== null) {
            $this->setEcho($echo);
        }
    }
    
    public function setPos($x, $y)
    {
        $this->xPos = $x;
        $this->yPos = $y;
        return $this;
    }
    
    public function setBgColor($color)
    {
        $this->bgColor = $color;
        return $this;
    }
    
    public function setFgColor($color)
    {
        $this->fgColor = $color;
        return $this;
    }

    /**
     * Show a list of options and prompt the user to select one of them.
     *
     * @return string       Selected option
     */
    public function show()
    {
        // Show prompt text and available options
        $console = $this->getConsole();
        if ($this->xPos == null || $this->yPos == null) {
            $this->xPos = 0;
            $this->yPos = 0;
        }
        
        $console->setPos($this->xPos, ($this->yPos=$this->yPos+1));
        $console->write($this->promptText, $this->fgColor, $this->bgColor);
        foreach ($this->options as $k => $v) {
            $console->setPos($this->xPos, ($this->yPos=$this->yPos+1));
            $console->write('  ' . $k . ') ' . $v, $this->fgColor, $this->bgColor);
        }

        //  Prepare mask
        $mask = implode("", array_keys($this->options));
        if ($this->allowEmpty) {
            $mask .= "\r\n";
        }

        // Prepare other params for parent class
        $this->setAllowedChars($mask);
        $oldPrompt        = $this->promptText;
        $oldEcho          = $this->echo;
        $this->echo       = false;
        $this->promptText = null;

        // Retrieve a single character
        $response = parent::show();

        // Restore old params
        $this->promptText = $oldPrompt;
        $this->echo       = $oldEcho;

        // Display selected option if echo is enabled
        if ($this->echo) {
            if (isset($this->options[$response])) {
                $console->setPos($this->xPos, ($this->yPos=$this->yPos+1));
                $console->writeLine($this->options[$response], $this->fgColor, $this->bgColor);
            } else {
                $console->setPos($this->xPos, ($this->yPos=$this->yPos+1));
                $console->writeLine();
            }
        }

        $this->lastResponse = $response;
        return $response;
    }

    /**
     * Set allowed options
     *
     * @param array|\Traversable $options
     * @throws Exception\BadMethodCallException
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof \Traversable) {
            throw new Exception\BadMethodCallException(
                'Please specify an array or Traversable object as options'
            );
        }

        if (!is_array($options)) {
            $this->options = [];
            foreach ($options as $k => $v) {
                $this->options[$k] = $v;
            }
        } else {
            $this->options = $options;
        }
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
    
}
