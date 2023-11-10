<?php

/**
 * This class defines a Minecraft world.
 */

class World
{
	private string $name;
	private ?string $path;
	private ?string $address;
	private array $players;

	/**
	 * Constructs a world from 
	 */
	public function __construct(array $worldData)
	{
		$this->name = isset($worldData["Name"]) ? $worldData["Name"] : "New World";
		$this->path = isset($worldData["Path"]) ? $worldData["Path"] : null;
		$this->address = isset($worldData["Address"]) ? $worldData["Address"] : null;
		$this->players = $this->getPlayers();
	}

	public function __destruct()
	{
	}

	/**
	 * PHP magic GET function
	 */
	public function __get($name)
	{
		return $this->$name;
	}

	/**
	 * Retrieves and displays all players for the world as a HTML string.
	 * If any errors occur, an appropriate message is displayed.
	 */
	public function displayPlayers(): string
	{
		$html = '';
		if (gettype($this->players) == 'string') {
			$html = "<p>$this->players</p>";
		} else {
			// echo '<pre>' . print_r($this->players, true) . '</pre>';
			foreach($this->players as $player) {
				echo '<img class="player-skin" src="' . $player->getPlayerHead() . '">';
			}
		}

		return $html;
	}

	/**
	 * Looks through the world's playerdata directory and finds all .dat files to generate a list of players from the server.
	 * Parses each player and returns each player as a JSON object.
	 * 
	 */
	private function getPlayers(): array
	{
		$players = array();

		if (!is_null($this->path)) {
			if (is_dir($this->path)) {
				// check that playerdata folder exists
				if (is_dir($path = "$this->path/$this->name/playerdata")) {
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
			echo 'This world has no player data.';
		}

		return $players;
	}

	/**
	 * Retrieves the stats of the world and returns them as an array.
	 */
	public function getWorldStats(string $name): ?array
	{
		$stats = null;

		return $stats;
	}
}
