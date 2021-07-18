<?php namespace Premmerce\SDK\V2\FileManager;


class FileManager{

	/**
	 * @var string
	 */
	private $mainFile;


	/**
	 * @var string
	 */
	private $pluginDirectory;


	/**
	 * @var string
	 */
	private $pluginName;


	/**
	 * @var string
	 */
	private $pluginUrl;

	/**
	 * @var string
	 */
	private $themeDirectory;

	/**
	 * PluginManager constructor.
	 *
	 * @param string $mainFile
	 * @param string|null $themeDirectory
	 */
	public function __construct($mainFile, $themeDirectory = null){
		$this->mainFile        = $mainFile;
		$this->pluginDirectory = plugin_dir_path($this->mainFile);
		$this->pluginName      = basename($this->pluginDirectory);
		$this->themeDirectory  = $themeDirectory?: $this->pluginName;
		$this->pluginUrl       = plugin_dir_url($this->getMainFile());
	}


	/**
	 * @return string
	 */
	public function getPluginDirectory(){
		return $this->pluginDirectory;
	}

	/**
	 * @return string
	 */
	public function getPluginName(){
		return $this->pluginName;
	}

	/**
	 * @return string
	 */
	public function getMainFile(){
		return $this->mainFile;
	}

	/**
	 * @return string
	 */
	public function getPluginUrl(){
		return $this->pluginUrl;
	}

	/**
	 * @param string $__template
	 * @param array $__variables
	 */
	public function includeTemplate($__template, array $__variables = []){
		if($__template = $this->locateTemplate($__template)){
			extract($__variables);
			include $__template;
		}
	}


	/**
	 * @param string $template
	 * @param array $variables
	 *
	 * @return string
	 */
	public function renderTemplate($template, array $variables = []){
		ob_start();
		$this->includeTemplate($template, $variables);
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * @param string $file
	 *
	 * @return string
	 */
	public function locateAsset($file){
		return $this->pluginUrl . 'assets/' . $file;
	}


	/**
	 * @param string $template
	 *
	 * @return string
	 */
	public function locateTemplate($template){

		if(strpos($template, 'frontend/') === 0){

			$frontendTemplate = str_replace('frontend/', '', $template);

			if($file = locate_template($this->themeDirectory . '/' . $frontendTemplate)){

				return $file;
			}
		}


		return $this->pluginDirectory . 'views/' . $template;
	}

}