<?php
require("player.php");

/**
 * This class manages all core functions for the site regarding retrieval of statistics and settings from settings.json.
 * 
 * @property array $settings The decoded settings.JSON file.
 */
class Settings
{
    private array $settings;

    /**
     * Reads the settings file and returns it as an iterable JSON object.
     */
    public function __construct()
    {
        $file = file_get_contents("settings.json");
        $this->settings = json_decode($file, true);
    }

    /**
     * Retrieves the data contained in the Worlds key in settings.json
     * @return ?array The contents of the Worlds key as an array or null if an error ocurred.
     */
    public function getWorlds(): ?array
    {
        return $this->settings["Worlds"] ?? null;
    }

    /**
     * Checks that the given world exists.
     * If it does, it retrieves the data for the world with the specified name.
     * @param string $name The name of the world.
     */
    public function checkWorld(?string $name): ?array
    {
        $world = null;

        if (!is_null($name) && !is_null($this->settings["Worlds"] ?? null)) {
            foreach ($this->settings["Worlds"] as $w) {
                if ($name == $this->checkJSONKey("Name", $w)) {
                    $world = $w;
                    break;
                }
            }
        }

        return $world;
    }

    /**
     * checks if the specified key in the given array exists. if it does, it returns its value. Else, it returns false.
     */
    private function checkJSONKey(string $key, array $obj): mixed
    {
        if (is_null($result = ($obj[$key] ?? null))) {
            echo "Missing key <code>\"$key\"</code> in <code>settings.json</code>. Check that the key is spelt correctly and is not omitted.";
            $result = false;
        }
        return $result;
    }
}
