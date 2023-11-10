<?php
include("page_components/header.php");
include("world.php");
$world = null;
if (!is_null($worldData = $settings->checkWorld($_GET['world']))) {
	$world = new World($worldData);
}
?>
<main>
	<?php if (is_null($world)) : ?>
		<p>The specified world does not exist.</p>
	<?php else : ?>
		<h2><?= $world->__get("name") ?></h2>
		<h4>Server: <?= $world->__get("address") ?></h3>
		<div id="player_list">
			<h3>Players</h3>
			<!-- <p>Retrieve player names and data.</p> -->
			<?= $world->displayPlayers() ?>
		</div>

		<div id="world_stats">
			<h3>World Stats</h3>
		</div>

	<?php endif; ?>
</main>

<?php include("page_components/footer.php"); ?>