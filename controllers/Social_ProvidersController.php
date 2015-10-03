<?php

/**
 * Social plugin for Craft
 *
 * @package   Craft Social
 * @author    Benjamin David
 * @copyright Copyright (c) 2015, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class Social_ProvidersController extends BaseController
{
	// Public Methods
	// =========================================================================

	public function actionIndex()
	{
		$variables['providers'] = craft()->social_providers->getProviders(false);

		$this->renderTemplate('social/providers/_index', $variables);
	}
}
