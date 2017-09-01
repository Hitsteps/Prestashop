<?php
/**
*  @author Hitsteps.com <sales@hitsteps.com>
*  @copyright  2010-2016 Hitsteps.com
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Class allow to display tpl on the FO
 */
class BWDisplay extends FrontController
{
	// Assign template, on 1.4 create it else assign for 1.5
	public function setTemplate($template)
	{
		if (_PS_VERSION_ >= '1.5')
			parent::setTemplate($template);
		else
			$this->template = $template;
	}

	// Overload displayContent for 1.4
	public function displayContent()
	{
		parent::displayContent();

		echo Context::getContext()->smarty->fetch($this->template);
	}
}
