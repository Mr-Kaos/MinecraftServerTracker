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
	 * Retrieves the data for the world with the specified name.
	 */
	public function getWorld(string $name): ?array
	{
		$world = null;

		if (!is_null($this->settings["Worlds"] ?? null)) {
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
	 * Retrieves a list of all players for the specified world and returns each player name as a JSON object.
	 */
	public function getWorldPlayers(array $world): ?string
	{
		$players = array();

		if ($path = $this->checkJSONKey('Path', $world)) {
			$path = $path . '/' . $world["Name"];
			if (is_dir($path)) {
				// check that playerdata folder exists
				if (is_dir($path = "$path/playerdata")) {
					$dh = opendir($path);
					while (($file = readdir($dh)) !== false) {
						if (!is_dir($file) && (substr($file, strlen($file) - 4, 4)) == '.dat') {
							$player = new Player(substr($file, 0, strlen($file) - 4));
							array_push($players, $player);
						}
					}
				}
			} else {
				echo "The World folder cannot be found at '$path'.";
			}
		}

		return json_encode($players);
	}

	/**
	 * Retrieves 
	 */
	public function getWorldStats(string $name): ?array
	{
		$stats = null;

		return $stats;
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

	/**
	 * Performs a cURL request to sessionserver.mojang.com for the specified player's data.
	 */
	private function getPlayerInfo(string $uuid)
	{
		$curl = curl_init("https://sessionserver.mojang.com/session/minecraft/profile/$uuid?unsigned=false");
		curl_setopt($curl, CURLOPT_HTTPGET, 'GET');

		$json = json_decode(curl_exec($curl));
		echo '<pre>' . print_r($json, true) . '</pre>';
		curl_exec($curl);
		var_dump(curl_getinfo($curl));
	}

	private function readPlayersFile(string $worldPath)
	{

		// readfile();
	}
}
