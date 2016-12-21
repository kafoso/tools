<?php
namespace Kafoso\Tools\HTML;

use Kafoso\Tools\Debug\VariableDumper;

class ViewRenderer
{
    private $_outputBufferLevel = null;
    private $_templatePath = null;
    private $_baseDirectory = null;
    private $_isShutDown = false;
    private $_isPrintingOutputBufferOnShutdown = true;
    private $_parent = null;

    /**
     * @param $templatePath ?string
     * @param $additionalVars null|array|object         Object must be instance of \Kafoso\Tools\HTML\ViewRenderer.
     * @throws \InvalidArgumentException
     */
    public function __construct($templatePath = null, $additionalVars = null)
    {
        if (!is_null($templatePath)) {
            $this->setTemplatePath($templatePath);
            if (false == is_null($additionalVars)) {
                $this->registerAdditionalVars($additionalVars);
            }
        }
    }

    public function __destruct()
    {
        $this->closeOutputBuffers();
    }

    /**
     * @param null $templatePath
     * @param array|object|null $additionalVars         An array or instance of this class.
     * @throws \RuntimeException
     * @return string
     */
    public function render($templatePath = null, $additionalVars = null)
    {
        if (false == is_null($templatePath)) {
            if (is_null($additionalVars)) {
                $additionalVars = $this->getPublicVariables(); // Inherit
            }
            $childViewRenderer = new ViewRenderer($templatePath, $additionalVars);
            $childViewRenderer->_parent = $this;
            $childViewRenderer->setBaseDirectory($this->getBaseDirectory());
            $childViewRenderer->guardAgainstEndlessLoops();
            return $childViewRenderer->render();
        }

        $this->validateBaseDirectory($this->getBaseDirectory());
        $templatePathAbsolute = $this->getBaseDirectory() . "/" . ltrim($this->_templatePath);
        $this->validateTemplateFile($templatePathAbsolute);

        register_shutdown_function([$this, 'shutdown']);
        try {
            ob_start();
            $this->_outputBufferLevel = ob_get_level();
            require($templatePathAbsolute);
            $output = ob_get_contents();
            ob_end_clean();
        } catch (\Exception $e) {
            $this->closeOutputBuffers();
            throw $e;
        }

        return $output;
    }

    public function shutdown()
    {
        if (false == $this->_isShutDown) {
            $this->closeOutputBuffers();
            $this->_isShutDown = true;
        }
    }

    public function setBaseDirectory($baseDirectory)
    {
        $this->_baseDirectory = realpath(rtrim($baseDirectory, '/'));
        return $this;
    }

    public function setIsPrintingOutputBufferOnShutdown($isPrintingOutputBufferOnShutdown)
    {
        $this->_isPrintingOutputBufferOnShutdown = (bool)$isPrintingOutputBufferOnShutdown;
        return $this;
    }

    public function setTemplatePath($templatePath)
    {
        if (preg_match('/\.(.+)\//', $templatePath)) {
            throw new \RuntimeException(sprintf(
                "Parent traversal (\".\" or \"..\") is not allowed. Template path: %s",
                $templatePath
            ));
        }
        $this->_templatePath = ltrim($templatePath, '/');
        return $this;
    }

    public function getBaseDirectory()
    {
        return $this->_baseDirectory;
    }

    public function getPublicVariables()
    {
        $reflectionObject = new \ReflectionObject($this);
        $array = [];
        foreach ($reflectionObject->getProperties() as $property) {
            if ($property->isPublic()) {
                $array[$property->getName()] = $property->getValue($this);
            }
        }
        return $array;
    }

    public function getTemplatePath()
    {
        return $this->_templatePath;
    }

    protected function registerAdditionalVars($additionalVars)
    {
        if (is_object($additionalVars)) {
            if (get_class($additionalVars) !== get_class($this)) {
                throw new \InvalidArgumentException(sprintf(
                    "Invalid parameter \"%s\". Expected object to be instance of \\%s. Found: \\%s",
                    '$additionalVars',
                    get_class($this),
                    get_class($additionalVars)
                ));
            }
            $additionalVars = $additionalVars->getPublicVariables();
        }
        if (is_array($additionalVars)) {
            foreach ($additionalVars as $k => $v) {
                if (is_string($k) && preg_match('/^[a-zA-Z][\w]*$/', $k)) {
                    $this->$k = $v;
                }
            }
        } elseif (false === is_null($additionalVars)) {
            throw new \InvalidArgumentException(sprintf(
                "Parameter \"%s\" is invalid. Expected one of: [null,array,object]. Found: %s",
                '$additionalVars',
                VariableDumper::found($additionalVars)
            ));
        }
    }

    protected function validateBaseDirectory($baseDirectoryPath)
    {
        if (false == file_exists($baseDirectoryPath)) {
            throw new \RuntimeException(sprintf(
                "Base directory does not exist on path: %s",
                $baseDirectoryPath
            ));
        }
        if (false == is_dir($baseDirectoryPath)) {
            throw new \RuntimeException(sprintf(
                "Base directory is invalid; not a directory: %s",
                $baseDirectoryPath
            ));
        }
    }

    protected function validateTemplateFile($templatePathAbsolute)
    {
        if (false == file_exists($templatePathAbsolute)) {
            throw new \RuntimeException(sprintf(
                "Template does not exist on path: %s",
                $templatePathAbsolute
            ));
        }
        if (false == is_file($templatePathAbsolute)) {
            throw new \RuntimeException(sprintf(
                "Template file location is invalid; not a file: %s",
                $templatePathAbsolute
            ));
        }
    }

    protected function closeOutputBuffers()
    {
        if ($this->_outputBufferLevel) {
            while (ob_get_level() >= $this->_outputBufferLevel) {
                if ($this->_isPrintingOutputBufferOnShutdown) {
                    ob_end_flush();
                } else {
                    ob_end_clean();
                }
            }
        }
        $this->_outputBufferLevel = null;
    }

    protected function guardAgainstEndlessLoops()
    {
        $templatePathsAbsolute = [];
        $templatePathsAbsolute[] = $this->getBaseDirectory() . "/" . $this->getTemplatePath();
        $parent = $this->_parent;
        while ($parent instanceof ViewRenderer) {
            $templatePathAbsolute = $parent->getBaseDirectory() . "/" . $parent->getTemplatePath();
            if (in_array($templatePathAbsolute, $templatePathsAbsolute)) {
                throw new \RuntimeException(sprintf(
                    "Endless loop prevented. The same template exists two or more times: %s",
                    $templatePathAbsolute
                ));
            }
            $templatePathsAbsolute[] = $templatePathAbsolute;
            $parent = $parent->_parent;
        }
    }
}
