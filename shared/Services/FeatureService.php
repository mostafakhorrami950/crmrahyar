<?php
namespace Shared\Services;

use Shared\Repositories\SettingsRepository;

class FeatureService
{
    private SettingsRepository $settings;
    private array $flags = [];
    private bool $loaded = false;

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    public function isEnabled(string $featureKey): bool
    {
        if (!$this->loaded) {
            $this->loadFlags();
        }
        return isset($this->flags[$featureKey]) && (bool)$this->flags[$featureKey];
    }

    public function enable(string $featureKey): void
    {
        $this->settings->set("feature_{$featureKey}", '1', 'boolean', 'features');
        $this->flags[$featureKey] = true;
    }

    public function disable(string $featureKey): void
    {
        $this->settings->set("feature_{$featureKey}", '0', 'boolean', 'features');
        $this->flags[$featureKey] = false;
    }

    public function getConfig(string $featureKey, $default = null)
    {
        return $this->settings->get("feature_{$featureKey}_config", $default);
    }

    public function all(): array
    {
        if (!$this->loaded) $this->loadFlags();
        return $this->flags;
    }

    private function loadFlags(): void
    {
        $this->loaded = true;
        $rows = $this->settings->getGroup('features');
        foreach ($rows as $key => $value) {
            $this->flags[str_replace('feature_', '', $key)] = (bool)$value;
        }
    }
}