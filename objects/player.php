<?php

const CACHE_DIR = 'data/usercache/';
/**
 * Defines a player object for use on the website.
 */

class Player
{
    private string $uuid;
    private string $name;
    private ?string $skinURI;
    private array $advancements;

    public function __construct(string $uuid, string $name = "Player")
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->skinURI = null;
        $this->getUserData($uuid);
        $this->advancements = Array();
    }

    /**
     * PHP magic GET function
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * Gets the texture of a player's head. If the player does not have a skin, the default steve skin is loaded.
     */
    public function getPlayerHead()
    {
        $src = $this->skinURI ?? CACHE_DIR . "default.png";
        return $src;
    }

    /**
     * Displays All stats for the player.
     * Stats displayed are controlled by the configuration in the settings.json.
     */
    public function displayStats(World $world): string
    {
        $rawStats = $this->readStatsFile($world->getWorldPath() . 'stats/' . $this->uuid . '.json', $world->__get('statFilters'));
        $html = '';

        foreach ($rawStats as $category => $data) {
            $sum = 0;
            $list = '';
            $catName = explode(':', $category)[1];
            $html .= '<div><h2>' . $catName . '</h2>';
            foreach ($data as $item => $val) {
                $sum += $val;
                $list .= '<li>' . explode(':', $item)[1] . ": $val</li>";
            }

            $html .= "<p>Total $catName: <b>$sum</b></p>";
            $html .= "<ul>$list</ul>";

            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Displays all advancements the player has completed and not completed.
     */
    public function displayAdvancements(World $world): string
    {
        $this->readAdvancements($world, $world->getWorldPath() . 'advancements/' . $this->uuid . '.json');
        
        $html = '<table><caption>Advancements</caption>';

        foreach ($this->advancements as $category => $advancements) {
            $html .= '<tr><th colspan="2">' . $category . '</th></tr>';
            foreach ($advancements as $advancement => $done) {
                $status = '';
                if ($done == '') {
                    $status = 'In Progress';
                } elseif ($done == 1) {
                    $status = '&#10003;';
                }
                $html .= "<tr><td>$advancement</td><td>$status</td></tr>";
            }
        }

        $html .= "</table>";

        return $html;
    }

    /**
     * Retrieves and returns the playtime as a string. Formats the playtime into days:hours:mins.
     */
    public function getPlayTime(string $worldPath): string
    {
        $stats = $this->readStatsJSON($worldPath, ["minecraft:custom"], ["minecraft:total_world_time"]);
        $playtime = "0 mins";
        if (!empty($stats)) {
            $secs = $stats["minecraft:custom"]["minecraft:total_world_time"] / 20;
            $time = new DateTime('@0');
            $time2 = new DateTime('@' . $secs);
            $playtime = $time->diff($time2)->format('%a days, %hh %im %ss');
            $playtime .= '<br>' . round($secs / 60 / 60, 2) . 'hrs';
        }

        return $playtime;
    }

    /**
     * Reads the cached user data. If no user data is cached, it retrieves it from the Mojang servers.
     */
    private function getUserData(string $uuid)
    {
        if (!$this->isCached($uuid)) {
            $this->cacheUserData($uuid);
        }

        if (!file_exists(CACHE_DIR . "$uuid.png")) {
            $this->cacheSkin($uuid);
        }

        $json = json_decode(file_get_contents(CACHE_DIR . "$uuid.json"), true);
        $this->name = $json['name'];
        $this->skinURI = file_exists(CACHE_DIR . "$uuid.png") ? CACHE_DIR . "$uuid.png" : null;
    }

    /**
     * Retrieves the user data from Mojang along with their skin.
     * Performs a cURL request to sessionserver.mojang.com for the specified player's data.
     */
    private function cacheUserData(string $uuid)
    {
        $curl = curl_init("https://sessionserver.mojang.com/session/minecraft/profile/$uuid?unsigned=false");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 'GET');

        if (empty($userData = curl_exec($curl))) {
            echo "User with UUID '$uuid' does not exist on Mojang's servers. <br>";
            $customData = ["id" => str_replace('-', '', $uuid), "name" => "Player", "properties" => [["name" => "textures", "value" => null]]];
            $userData = json_encode($customData);
        }

        $userFile = fopen(CACHE_DIR . "$uuid.json", 'w');
        fwrite($userFile, $userData);
        fclose($userFile);
    }

    private function cacheSkin(string $uuid): void
    {
        $dataJSON = json_decode(file_get_contents(CACHE_DIR . "$uuid.json"), true);

        $skinData = json_decode(base64_decode($dataJSON['properties'][0]['value']), true);
        if (!empty($skinData)) {
            $texture = file_get_contents($skinData['textures']['SKIN']['url']);
            file_put_contents(CACHE_DIR . "$uuid.png", $texture);

            $src = CACHE_DIR . "$uuid.png";
            $img = imagecreatefrompng($src);
            $crop = imagecrop($img, ['x' => 8, 'y' => 8, 'width' => 8, 'height' => 8]);
            imagepng($crop, $src);
        }
    }

    private function isCached(string $uuid): bool
    {
        return file_exists(CACHE_DIR . "$uuid.json");
    }

    /** 
     * Reads the player's stat file from the current world.
     */
    private function readStatsJSON(string $worldPath, array $categories = null, array $keys = null): array
    {
        $stats = array();
        if (file_exists($worldPath . "/stats/$this->uuid.json")) {
            $json = json_decode(file_get_contents($worldPath . "/stats/$this->uuid.json"), true);

            foreach ($categories as $cat) {
                foreach ($keys as $key) {
                    $stats[$cat][$key] = $json["stats"][$cat][$key];
                }
            }
        }
        return $stats;
    }

    /**
     * Reads the player's stats file from, the given world folder.
     * Uses the filters retrieved from the settings JSON to filter what stats are collected.
     * @param string $path The filepath to the player's stats file.
     * @param array The filters for selecting stats defined in the settings JSON file.
     * @return array The filtered stats.
     */
    private function readStatsFile(string $path, array $filters): array
    {
        $filteredStats = array();
        if ($stats = file_get_contents($path)) {
            $stats = json_decode($stats, true);

            foreach ($filters as $filter => $item) {
                if (isset($stats['stats'][$filter])) {
                    if ($item == '*') {
                        $filteredStats[$filter] = $stats['stats'][$filter];
                    } elseif (is_array($item)) {
                        foreach ($item as $subItem) {
                            if (isset($stats['stats'][$filter][$subItem])) {
                                $filteredStats[$filter][$subItem] = $stats['stats'][$filter][$subItem];
                            }
                        }
                    } elseif (!is_null($item)) {
                        echo "Error: Invalid Stat filter syntax for filter '$filter'. Filter items must be stored in a list.";
                    }
                    // var_dump($stats['stats'][$filter]);
                    // echo '<hr>';
                }
            }
        } else {
            echo 'Failed to read stats file.';
        }
        // echo '<pre>' . print_r($filteredStats, true), '</pre>';

        return $filteredStats;
    }

    /**
     * Reads the player's advancements JSON for the specified world and finds which ones have been completed.
     * @param World $world The world to read the advancements from
     * @param string $path The path to the advancements file
     */
    private function readAdvancements(World $world, string $path): array
    {
        /**
         * Filters the player's advancements to only include non-recipe or root advancements.
         */
        function filter(mixed $key) {
            return str_contains($key, 'minecraft:') && !str_contains($key, ':recipe');
        }

        $advancements = file_get_contents($path);

        if (!empty($advancements)) {
            $advancements = json_decode($advancements, true);

            $advancements = array_filter($advancements, "filter", ARRAY_FILTER_USE_KEY);

            $this->advancements = $world->getAdvancements();

            foreach ($this->advancements as $category => $item) {
                foreach ($item as $advancement => $status) {
                    $key = "minecraft:$category/$advancement";
                    if (array_key_exists($key, $advancements)) {
                        $this->advancements[$category][$advancement] = $advancements[$key]['done'];
                    }
                }
            }
        }

        // Needs refactoring, should not return a class property.
        return $this->advancements;
    }
}
