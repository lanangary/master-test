<?php

namespace JuiceBox\Core;

abstract class Module
{
    protected $module = null;
    protected $name;
    protected $post;

    /**
     * Returns processed module
     *
     * @param array $module
     */
    public function __construct($module, $name, $post)
    {
        $this->setModule($module);
        $this->setName($name);
        $this->setPost($post);
        $this->processModule();
    }

    /**
     * Set Module
     *
     * @param array $module
     * @return \JuiceBox\Core\Module
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Returns processed module data, filter has been applied here to allow the data to be manipulated before it is rendered out.
     * @return array
     */
    public function getModule()
    {
        return apply_filters("jb_module_{$this->name}_data", $this->module);
    }

    /**
     * There is a filter applied here to allow the template to be overrriden.
     * @return String the template for twig to call
     */
    public function getTemplate()
    {
        return apply_filters("jb_module_{$this->name}_template", $this->getTemplatePath());
    }

    public function getPath()
    {
        $themeDir = get_stylesheet_directory();
        $modulePath = $themeDir . '/src/' . $this->getNamespace();
        return str_replace( "\\", "/", $modulePath );
    }

    public function getUri()
    {
        $themeDirUri = get_stylesheet_directory_uri();
        $modulePathUri = $themeDirUri . '/src/' . $this->getNamespace();
        return str_replace( "\\", "/", $modulePathUri );
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get Name
     *
     * @return array
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set Post
     *
     * @param array $post
     * @return \JuiceBox\Core\Module
     */
    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get Post
     *
     * @return array
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Does any processing for this module.
     */
    public function processModule()
    {

    }

    /**
     * To String
     */
    public function __toString()
    {
        $context = \Timber::get_context();

        $context['module'] = $this->getModule();

        return \Timber::compile($this->getTemplatePath(), $context);
    }

    protected function getTemplatePath()
    {
        $base = $this->getNamespace();

        $base = str_replace( "JuiceBox\\Modules\\", "", $base );

        return "{$base}/template.twig";
    }

    protected function getNamespace()
    {
        $reflector = new \ReflectionClass($this);

        return $reflector->getNamespaceName();
    }
}
