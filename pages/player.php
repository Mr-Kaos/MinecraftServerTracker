<?php
include("components/header.php");
include("objects/world.php");
$world = null;
$player = null;
if (!is_null($worldData = $settings->checkWorld($_GET['world']))) {
    $world = new World($worldData);
    if (isset($_GET['uuid'])) {
        $player = $world->getPlayer($_GET['uuid']);
    }
}
?>
<main>
    <?php if (is_null($world)) : ?>
        <p>The specified world does not exist.</p>
    <?php elseif (is_null($player)): ?>
        <p>The specified player does not exist in this world.</p>
    <?php else : ?>
        <h2><?= $world->__get("name") ?> - <?= $player->__get('name') ?>'s Stats</h2>
        <div id="player_advancements">
            <?= $player->displayAdvancements($world)?>
        </div>
        <div id="player_stats">
            <?= $player->displayStats($world)?>
        </div>

    <?php endif; ?>
</main>

<?php include("components/footer.php"); ?>