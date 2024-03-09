<?php
include("components/header.php");
// include("components/nav.php");
$worlds = $settings->getWorlds();
?>
<main>
	<h1>World List</h1>
	<div id="worlds">
		<?php
		if (!is_null($worlds)) {
			foreach ($worlds as $world) {
				if (is_null($name = $world["Name"] ?? null)) {
					echo "Invalid syntax in settings.json";
					break;
				}
				echo '<a href="world?world=' . $name . '">' . $name . '</a><br>';
			}
		}
		?>
	</div>
</main>

<?php include("components/footer.php"); ?>