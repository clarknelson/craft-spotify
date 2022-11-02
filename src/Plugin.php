<?php
namespace clarknelson\spotify;

use craft\web\twig\variables\CraftVariable;
use yii\base\Event;

use clarknelson\spotify\models\Settings;
use clarknelson\spotify\services\SpotifyService;

class Plugin extends \craft\base\Plugin
{
    public bool $hasCpSettings = true;

    const EDITION_LITE = 'lite';
    const EDITION_PRO = 'pro';

    const EDITION_STANDARD = 'standard';


    public static function editions(): array
    {
        return [
            self::EDITION_STANDARD
            // self::EDITION_PRO,
            // self::EDITION_LITE
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->setComponents([
            'spotify' => SpotifyService::class,
        ]);

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $e) {
                /** @var CraftVariable $variable */
                $variable = $e->sender;
                
                // Attach a service:
                $variable->set('spotify', SpotifyService::class);
            }
        );
    }

    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }
    protected function settingsHtml(): ?string
    {
        return \Craft::$app->getView()->renderTemplate(
            'craft-spotify/settings',
            [ 'settings' => $this->getSettings() ]
        );
    }
}