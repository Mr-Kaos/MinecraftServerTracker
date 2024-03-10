# Minecraft Server Stats Site

This repository contains code to host your own basic website that displays the stats of player from your own Minecraft Java Edition servers.

This website runs on PHP 8.1 and above and can be set up locally or hosted externally.

## Features

Below is the list of planned features for this web app. For features currently in development, see [Currently in Development](#currently-in-development).

- [ ] Display statistics for players for multiple worlds.
- [ ] Advancement tracking
- [ ] Display a map of the world.
- [ ] Configurable settings to fine-tune what is visible to site visitors.
  - Don't want players to see a map of the world? Only want whitelisted players to view the statistics? Only want specific statistics to be displayed? These can all be configured in a JSON file.
- [ ] Graphs and charts to visualise the statistics of players against each other.

## How to Configure Worlds

### Enabling Advancement Tracking

To enable advancement tracking, you must provide the Minecraft client jar for the version of Minecraft that the server is using.
Once obtained, you can copy the full path to the client jar into the world's settings in the `settings.json` file under the `VersionJar` key.

Doing this will allow the application to extract all advancements available in that version of minecraft for the associated world and allow advancement tracking for all players.

### Adding Player Statistic Filters

There are several ways in which player statistics can be filtered and displayed.

Categories:
- None available in this current version.

Category Entries:
- `"*"` - All statistics in the specified category will be displayed.
- `"SUM *"` - Displays the sum of all items in the specified category, including ones not specified. [NOT CURRENTLY IMPLEMENTED]
- `"<namespace>:<item>"` - Displays the specified item from the specified category from the stats. For example, `"minecraft:stone"` displays its statistic for its category.

## Development Path

### Currently in Development:

- [ ] Core site framework
- [ ] Loading statistics from local save file
- [X] Advancement tracking

### To Be Developed:

- [ ] Player's Online status (per world)
- [ ] Player leader board/player stats comparison

## How the System Works

This system is designed to be a lightweight web application that only retrieves information when needed. Minimal to no JavaScript is used (currently) and all requests are handled through PHP.

To prevent clients from spamming the server with requests to read the Minecraft world's data, a global refresh limit is set to 5 seconds. This can be changed in the settings JSON. This means that any requests made to the website within the last five seconds of the previous update will not cause the application to refresh the world or player's data.

The system cycle is as follows:
1. User requests to view a world page.
2. Initialise world data
   1. Retrieve advancements from the Minecraft client jar (optional)
   2. Retrieve player data for each player who has played in the server using Mojang's API.
3. World page is loaded
   1. Cached players are displayed
   2. Cached world data is displayed
4. User requests to view player data for the world
   1. Application accesses player's data from the Minecraft world folder
   2. Application summarises and formats retrieved player data to be displayed in the browser
5. Player page is loaded
   1. Player's data related to the current world is displayed.
