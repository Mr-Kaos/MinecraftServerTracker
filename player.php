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
		$this->skinURI = null;
		$this->getUserData($uuid);
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
	 * retrieves the user data from Mojang along with their skin.
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
		echo $uuid;
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
