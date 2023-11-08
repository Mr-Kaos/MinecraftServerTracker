<?php
include("page_components/header.php");
// include("page_components/nav.php");
$world = $settings->getWorld($_GET['world']);
?>
<main>
	<?php if (is_null($world)) : ?>
		<p>The specified world does not exist.</p>
	<?php else : ?>
		<h2><?= $world["Name"] ?></h2>

		<div id="player_list">
			<h3>Players</h3>
			<p>Retrieve player names and data.</p>
			<?php $settings->getWorldPlayers($world) ?>
		</div>

		<div id="world_stats">
			<h3>World Stats</h3>
		</div>

	<?php endif; ?>
</main>

<?php include("page_components/footer.php"); ?>