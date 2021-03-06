<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\services;

use Craft;
use dukt\social\base\LoginProvider;
use dukt\social\events\RegisterLoginProviderTypesEvent;
use dukt\social\Plugin;
use yii\base\Component;
use dukt\social\base\LoginProviderInterface;

/**
 * The LoginProviders service provides APIs for managing login providers in Craft.
 *
 * An instance of the LoginProviders service is globally accessible in Craft via [[Plugin::loginProviders `Plugin::getInstance()->getLoginProviders()`]].
 *
 * @author  Dukt <support@dukt.net>
 * @since   1.0
 */
class LoginProviders extends Component
{
    // Constants
    // =========================================================================

    /**
     * @event RegisterLoginProviderTypesEvent The event that is triggered when registering login providers.
     */
    const EVENT_REGISTER_LOGIN_PROVIDER_TYPES = 'registerLoginProviderTypes';

    // Public Methods
    // =========================================================================

    /**
     * Disable a login provider by handle.
     *
     * @param string $handle
     *
     * @return bool
     */
    public function disableLoginProvider($handle): bool
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('social');
        $settings = $plugin->getSettings();

        $enabledLoginProviders = $settings->enabledLoginProviders;

        if (($key = array_search($handle, $enabledLoginProviders)) !== false) {
            unset($enabledLoginProviders[$key]);
        }

        $settings->enabledLoginProviders = $enabledLoginProviders;

        return Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->getAttributes());
    }

    /**
     * Enable a login provider by handle.
     *
     * @param string $handle
     *
     * @return bool
     */
    public function enableLoginProvider($handle): bool
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('social');
        $settings = $plugin->getSettings();

        $enabledLoginProviders = $settings->enabledLoginProviders;

        if (!in_array($handle, $enabledLoginProviders)) {
            $enabledLoginProviders[] = $handle;
        }

        $settings->enabledLoginProviders = $enabledLoginProviders;

        return Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->getAttributes());
    }

    /**
     * Get a login provider by handle.
     *
     * @param string    $handle
     * @param bool|true $enabledOnly
     *
     * @return LoginProvider|LoginProviderInterface|null
     */
    public function getLoginProvider($handle, $enabledOnly = true)
    {
        $loginProviders = $this->getLoginProviders($enabledOnly);

        foreach ($loginProviders as $loginProvider) {
            if ($loginProvider->getHandle() == $handle) {
                return $loginProvider;
            }
        }
    }

    /**
     * Get login providers.
     *
     * @param bool|true $enabledOnly
     *
     * @return array
     */
    public function getLoginProviders($enabledOnly = true): array
    {
        return $this->_getLoginProviders($enabledOnly);
    }

    /**
     * Get user mapping for a given provider.
     *
     * @param string $providerHandle
     *
     * @return array
     */
    public function getUserMapping(string $providerHandle): array
    {
        $loginProviderConfig = Plugin::$plugin->getLoginProviderConfig($providerHandle);

        $userMapping = [
            'email' => '{{ email }}',
            'username' => '{{ email }}',
        ];

        if (isset($loginProviderConfig['userMapping'])) {
            $userMapping = array_merge($userMapping, $loginProviderConfig['userMapping']);
        }

        return $userMapping;
    }

    // Private Methods
    // =========================================================================

    /**
     * Get login providers and instantiate them.
     *
     * @param bool $enabledOnly
     *
     * @return array
     */
    private function _getLoginProviders($enabledOnly): array
    {
        $loginProviderTypes = $this->_getLoginProviderTypes();

        $loginProviders = [];

        foreach ($loginProviderTypes as $loginProviderType) {
            $loginProvider = $this->_createLoginProvider($loginProviderType);

            if (!$enabledOnly || ($enabledOnly && $loginProvider->getIsEnabled())) {
                $key = substr($loginProviderType, strrpos($loginProviderType, "\\") + 1);
                $loginProviders[$key] = $loginProvider;
            }
        }

        ksort($loginProviders);

        return $loginProviders;
    }

    /**
     * Returns login provider types.
     *
     * @return array
     */
    private function _getLoginProviderTypes(): array
    {
        $loginProviderTypes = [
            'dukt\social\loginproviders\Facebook',
            'dukt\social\loginproviders\Google',
            'dukt\social\loginproviders\Twitter',
        ];

        $eventName = self::EVENT_REGISTER_LOGIN_PROVIDER_TYPES;

        $event = new RegisterLoginProviderTypesEvent([
            'loginProviderTypes' => $loginProviderTypes
        ]);

        $this->trigger($eventName, $event);

        return $event->loginProviderTypes;
    }

    /**
     * Instantiates an OAuth provider.
     *
     * @param $socialLoginProviderType
     *
     * @return LoginProviderInterface
     */
    private function _createLoginProvider($socialLoginProviderType): LoginProviderInterface
    {
        return new $socialLoginProviderType;
    }
}
