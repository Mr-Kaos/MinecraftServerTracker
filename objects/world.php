<?php

/**
 * This class defines a Minecraft world.
 */

require_once('zipArchiveExt.php');

const WORLD_CACHE = 'data/worldcache/';
const CACHE_FILE = 'data.json';

class World
{
    private string $name;
    private ?string $path;
    private ?string $worldFolder;
    private ?string $address;
    private array $players;
    private array $statFilters;

    /**
     * Constructs a world from 
     */
    public function __construct(array $worldData)
    {
        $this->name = isset($worldData["Name"]) ? $worldData["Name"] : "New World";
        $this->path = isset($worldData["Path"]) ? $worldData["Path"] : null;
        preg_match('/[^\/\\\\]+$/m', $this->path, $matches);
        $this->worldFolder = empty($matches) ? null : $matches[0];
        $this->address = isset($worldData["Address"]) ? $worldData["Address"] : null;
        $this->statFilters = isset($worldData["StatFilters"]) ? $worldData["StatFilters"] : null;
        $this->updateWorldCache($worldData);
        $this->players = array();
        $this->readWorldProperties();
    }

    public function __destruct()
    {
    }

    /**
     * PHP magic GET function
     * @deprecated
     */
    public function __get($name)
    {
        return $this->$name;
    }

    public function getWorldPath(): string
    {
        return $this->path . '/' . $this->worldFolder . '/';
    }

    /**
     * Retrieves and displays all players for the world as a HTML string.
     * If any errors occur, an appropriate message is displayed.
     */
    public function displayPlayers(): string
    {
        $this->players = $this->getPlayers();
        $html = '';
        if (gettype($this->players) == 'string') {
            $html = "<p>$this->players</p>";
        } else {
            // echo '<pre>' . print_r($this->players, true) . '</pre>';
            foreach ($this->players as $player) {
                $html .= '<div class="player-container">';
                $html .= '<img class="player-skin" src="' . $player->getPlayerHead() . '"><div>';
                $html .= '<h3>' . $player->__get('name') . '</h3>';
                $html .= '<b>Playtime: ' . $player->getPlayTime("$this->path/$this->worldFolder") . '</b>';
                $html .= '<a href="player.php?uuid=' . $player->__get('uuid') . '&world=' . $this->name . '">View all stats</a>';
                $html .= '</div></div>';
            }
        }

        return $html;
    }

    /**
     * Gets the player with the specified UUID.
     * @param string $uuid The UUID of the player to retrieve.
     */
    public function getPlayer(string $uuid): ?Player
    {
        $player = null;
        if (!is_null($this->path)) {
            if (is_dir($this->path)) {
                if (file_exists("$this->path/$this->worldFolder/playerdata/$uuid.dat")) {
                    $player = new Player($uuid);
                }
            } else {
                echo "The World folder cannot be found at '$this->path'.";
            }
        } else {
            echo 'This world has no player data.';
        }
        return $player;
    }

    /**
     * Looks through the world's playerdata directory and finds all .dat files to generate a list of players from the server.
     * Parses each player and returns each player as a JSON object.
     * 
     */
    private function getPlayers(): array
    {
        $players = array();

        if (!is_null($this->path) && !is_null($this->worldFolder)) {
            if (is_dir($this->path)) {
                // check that playerdata folder exists
                if (is_dir($path = "$this->path/$this->worldFolder/playerdata")) {
                    $dh = opendir($path);
                    while (($file = readdir($dh)) !== false) {
                        if (!is_dir($file) && (substr($file, strlen($file) - 4, 4)) == '.dat') {
                            $player = new Player(substr($file, 0, strlen($file) - 4));
                            array_push($players, $player);
                        }
                    }
                    // $players = json_encode($players);
                } else {
                    echo 'This world has no player data.';
                }
            } else {
                echo "The World folder cannot be found at '$this->path'.";
            }
        } else {
            echo 'This world does not exist on the disk. Check that the path in the JSON is correct.';
        }

        return $players;
    }

    /**
     * Retrieves the stats of the world and returns them as an array.
     */
    public function getWorldStats(string $name): ?array
    {
        $stats = null;

        if (!file_exists('data/worldcache/' . $this->name . '.json')) {
            $this->cacheStats();
        }

        return $stats;
    }

    /**
     * Returns an array of all advancements available for this world.
     */
    public function getAdvancements(): array
    {
        $advancements = [];
        $advDir = WORLD_CACHE . $this->worldFolder . '/advancements/';

        foreach (scandir($advDir) as $dir) {
            if (is_dir("$advDir/$dir") && !($dir == '.' || $dir == '..')) {
                $advancements[$dir] = [];
                foreach (scandir("$advDir/$dir") as $file) {
                    if (is_file("$advDir/$dir/$file")) {
                        $advancements[$dir][str_replace('.json', '', $file)] = 0;
                    }
                }
            }
        }

        return $advancements;
    }

    /**
     * Reads the server's server.properties file to get information on the server.
     * currently only reads the world name and exits after it has been retrieved.
     */
    private function readWorldProperties()
    {
        $finished = false;
        if (!is_null($this->path) && is_dir($this->path)) {
            foreach (file($this->path . '/server.properties') as $line) {
                if ($line[0] !== '#') {
                    $split = explode('=', $line);

                    switch ($split[0]) {
                        case 'level-name':
                            $this->worldFolder = trim($split[1]);
                            $finished = true;
                            break;
                    }
                }
                if ($finished) {
                    break;
                }
            }
        }
    }

    private function updateWorldCache(array $worldData): void
    {
        $newData = [];
        $cache = "";
        $cachedData = [];
        $cachePath = WORLD_CACHE . $this->worldFolder;

        // Initialise cache folder. Create file and directory if it does not exist, else read it as JSON.
        if (is_dir($cachePath)) {
            // check cache
            if (file_exists($cachePath . '/' . CACHE_FILE)) {
                $cache = file_get_contents($cachePath . '/' . CACHE_FILE);
            }
            $cachedData = json_decode($cache, true) ?? [];
        } else {
            mkdir($cachePath);
        }

        // Initialise world data and version-specific data by looking at client jar.
        // If no client jar is given, ignore.
        if (array_key_exists("VersionJar", $worldData)) {
            preg_match('/[^\/\\\\]+(?=\.jar)/m', $worldData["VersionJar"], $matches);
            if (count($matches) == 1) {
                $jarName = $matches[0];
                $newData["LastUpdated"] = date_format(new DateTime(), 'Y-m-d G:i:s');
                $newData["ClientJarName"] = $jarName;

                // If client jar has changed, extract advancements again.
                if ((array_key_exists("ClientJarName", $cachedData) ? $cachedData["ClientJarName"] : null) !== $jarName) {
                    $this->extractAdvancements($worldData["VersionJar"], "$cachePath/advancements");
                }
            } elseif (count($matches) > 1) {
                echo "Invalid Client jar path given!";
                die();
            }
        }

        $newData = json_encode($newData, JSON_NUMERIC_CHECK);

        if ($newData !== $cache) {
            $handle = fopen($cachePath . '/' . CACHE_FILE, 'w');
            fwrite($handle, $newData);
        }
    }

    /**
     * This function extracts the advancements from the Minecraft client jar.
     * The jar should be defined in the settings.json in the VersionJar key.
     * 
     * The jar is only extracted if the filename of the jar changes since the last extract.
     * @param string $path The path to the minecraft client jar archive.
     */
    private function extractAdvancements(string $source, string $dest): bool
    {
        $success = false;
        $zip = new ZipArchiveExt;

        if ($zip->open($source) === true) {
            // Clear advancements folder if it exists.
            if (is_dir($dest)) {
                foreach (scandir($dest) as $dir) {
                    if (is_dir("$dest/$dir") && !($dir == '.' || $dir == '..')) {
                        foreach (scandir("$dest/$dir") as $file) {
                            // echo "$dest/$dir/$file <br>";
                            if (is_file("$dest/$dir/$file")) {
                                // echo "$file<br>";
                                unlink("$dest/$dir/$file");
                            }
                        }
                        rmdir("$dest/$dir");
                    }
                }
                rmdir($dest);
            }

            $errors = $zip->extractSubdirTo($dest, "data/minecraft/advancements/", ['recipes', 'recipes/brewing', 'recipes/building_blocks', 'recipes/combat', 'recipes/decorations', 'recipes/food', 'recipes/misc', 'recipes/redstone', 'recipes/tools', 'recipes/transportation',]);

            if (count($errors) > 0) {
                print "Failed extracting client jar. Errors: <pre>" . print_r($errors, true) . '</pre>';
            } else {
                $success = true;
            }

            $zip->close();
        } else {
            print "Failed opening client jar";
        }

        return $success;
    }

    /**
     * Cache's world stats to a data file.
     * The stats are typically the sum of all player stats.
     */
    private function cacheStats()
    {
        echo 'get and cache stats for world.';
    }
}
