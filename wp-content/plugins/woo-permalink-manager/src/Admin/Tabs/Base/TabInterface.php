<?php namespace Premmerce\UrlManager\Admin\Tabs\Base;

interface TabInterface
{

    /**
     * Register hooks
     */
    public function init();

    /**
     * Render tab content
     */
    public function render();

    /**
     * Returns tab label
     * @return string
     */
    public function getLabel();

    /**
     * Returns unique tab name
     */
    public function getName();

    /**
     * Is tab valid to render
     *
     * @return bool
     */
    public function valid();
}