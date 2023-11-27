<?php
include("components/header.php");
include("objects/world.php");
$world = null;
if (!is_null($worldData = $settings->checkWorld(isset($_GET['world']) ? $_GET['world'] : null))) {
	$world = new World($worldData);
}
?>
<main>
	<?php if (is_null($world)) : ?>
		<p>The specified world does not exist.</p>
	<?php else : ?>
		<h2><?= $world->__get("name") ?></h2>
		<h4>Server: <?= $world->__get("address") ?></h3>
		<h3>Players</h3>
		<div id="player_list">
			<?= $world->displayPlayers() ?>
		</div>

		<div id="world_stats">
			<h3>World Stats</h3>
		</div>

	<?php endif; ?>
</main>

<?php include("components/footer.php"); ?>