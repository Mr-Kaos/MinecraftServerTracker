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

### Adding Player Statistic Filters

There are several ways in which player statistics can be filtered and displayed.

Categories:



Category Entries:
- `"*"` - All statistics in the specified category will be displayed.
- `"SUM *"` - Displays the sum of all items in the specified category, including ones not specified.
- `"<namespace>:<item>"` - Displays the specified item from the specified category from the stats. For example, `"minecraft:stone"` displays its statistic for its category.


## Development Path

### Currently in Development:

- [ ] Core site framework
- [ ] Loading statistics from local save file
- [ ] Advancement tracking

### To Be Developed:

- 