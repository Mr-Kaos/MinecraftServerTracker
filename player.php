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

	public function __construct(string $uuid, string $name = "Player")
	{
		$this->uuid = $uuid;
		$this->name = $name;
		$this->getUserData($uuid);
	}

	/**
	 * Reads the cached user data. If no user data is cached, it retrieves it from the Mojang servers.
	 */
	private function getUserData(string $uuid)
	{
		if (!$this->isCached($uuid)) {
			$this->cacheUserData($uuid);
		}

		// file_get_contents(CACHE_DIR . "$uuid.json");
	}

	/**
	 * Decodes 
	 */
	private function cacheUserData(string $uuid)
	{
		if (!empty($userData = $this->getPlayerInfo($uuid))) {
			$dataJSON = json_decode($userData, true);
			// echo '<pre>' . print_r($userData, true) . '</pre>';
			// $userFile = fopen(CACHE_DIR . "$uuid.json", 'w');
			// fwrite($userFile, $userData);
			// fclose($userFile);
	
			$skinData = json_decode(base64_decode($dataJSON['properties'][0]['value']), true);
			echo '<pre>' . print_r($skinData, true) . '</pre>';
	
			$texture = file_get_contents($skinData['textures']['SKIN']['url']);
			file_put_contents(CACHE_DIR . "$uuid.png", $texture);
		} else {
			echo "User with UUID '$uuid' does not exist on Mojang's servers. <br>";
			$customData = ["id" => str_replace('-', '', $uuid), "name" => "Player", "properties" => ["name" => "textures", "value" => null]];
			$userData = json_encode($customData);
		}

		$userFile = fopen(CACHE_DIR . "$uuid.json", 'w');
		fwrite($userFile, $userData);
		fclose($userFile);
	}

	private function isCached(string $uuid): bool
	{
		return file_exists(CACHE_DIR . "$uuid.json");
	}

	/**
	 * Performs a cURL request to sessionserver.mojang.com for the specified player's data.
	 */
	private function getPlayerInfo(string $uuid): string | bool
	{
		$curl = curl_init("https://sessionserver.mojang.com/session/minecraft/profile/$uuid?unsigned=false");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 'GET');

		return curl_exec($curl);
	}
}
