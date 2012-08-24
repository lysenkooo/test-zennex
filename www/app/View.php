<?php
/**
 * View class allow make division between logic ana representation
 */
class View
{
    private $_vars = array();
    private $_template = '';

    public function __construct($templateName)
    {
        $this->_template = 'static/view/' . $templateName . '.phtml';
    }

    private function _getPart($partName)
    {
        $partFile = 'static/view/' . $partName . '.phtml';

        if ( file_exists($partFile) ) {
            extract($this->_vars);
            include($partFile);
        } else {
            throw new Exception(__FILE__ . ' : Part file not found' . $partName);
        }

        return ob_get_clean();
    }

    public function assign($name, $value)
    {
        $this->_vars[$name] = $value;
    }

    public function render($parts = array())
    {
        if ( file_exists($this->_template) ) {
            extract($this->_vars);

            foreach ($parts as $varName => $partName) {
                $$varName = $this->_getPart($partName);
            }

            include($this->_template);
        } else {
            throw new Exception(__FILE__ . ' : Layout not found ' . $this->_template);
        }
    }
}