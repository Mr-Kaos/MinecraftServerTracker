<?php

/**
 * This class defines a Minecraft world.
 */

class World
{
	private string $name;
	private ?string $path;
	private ?string $worldFolder;
	private ?string $address;
	private array $players;

	/**
	 * Constructs a world from 
	 */
	public function __construct(array $worldData)
	{
		$this->name = isset($worldData["Name"]) ? $worldData["Name"] : "New World";
		$this->path = isset($worldData["Path"]) ? $worldData["Path"] : null;
		$this->worldFolder = null;
		$this->address = isset($worldData["Address"]) ? $worldData["Address"] : null;
		$this->players = array();
		$this->readWorldProperties();
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

		return $stats;
	}

	/**
	 * Reads the server's server.properties file to get information on the server.
	 * currently only reads the world name and exits after it has been retrieved.
	 */
	private function readWorldProperties()
	{
		$finished = false;
		if (!is_null($this->path)) {
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
}
