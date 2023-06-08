<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$parsing_types = array("doctors_of_the_year" => "Docteur.e.s de l'année en cours", "phd_proposal" => "Propositions de sujets de thèse", "annu" => "Annuaire des doctorant.e.s", "phd_defense_by_ed" => "Soutenances à venir", "members_annu" => "Annuaire des encadrant.e.s");
$codes = array("41" => "ED Droit", "42" => "ED Entreprise Economie Société", "40" => "ED Sciences Chimiques", "154" => "ED Sciences de la Vie et de la Santé", "304" => "ED Sciences et environnements", "209" => "ED Sciences Physiques et de l'Ingénieur", "545" => "ED Sociétés, Politique, Santé Publique", "39" => "ED Mathématiques et Informatique");
?>


<div class="form-group">
	<label class="control-label" for="parsing">Type d'information</label>
	<select name="parsing" class="ccm-input-select">
		<?php foreach ($parsing_types as $key => $opt) { ?>
			<option value="<?php echo $key; ?>" <?php if (strcmp($parsing, $key) === 0) { ?>selected<?php } ?>> <?php echo $opt; ?></option>
		<?php } ?>
	</select>
<br/>
	<label class="control-label" for="filter">Filtre <sup class="fas fa-asterisk"></sup></label>
	<select name="filter" class="ccm-input-select">
		<option value="">Aucun</option>
		<?php foreach ($codes as $key => $opt) { ?>
			<option value="<?php echo $key; ?>" <?php if (strcmp($filter, $key) === 0) { ?>selected<?php } ?>> <?php echo $opt; ?></option>
		<?php } ?>
	</select>
	<br/><label class="control-label" for="year">Année affichée</label>
	<select name="year" class="ccm-input-select">
		<?php for($i=2016;$i<date("Y");$i++) { ?>
			<option value="<?php echo $i; ?>" <?php if (strcmp($year, $i) === 0) { ?>selected<?php } ?>> <?php echo $i; ?></option>
		<?php } ?>
	</select>
	<br/>
	<label class="control-label" for="details">Présentation détaillée</label>
	<select name="details" class="ccm-input-select">		
		<option value="True" <?php if (strcmp($details, "True") === 0) { ?>selected<?php } ?>> Oui</option>
		<option value="False" <?php if (strcmp($details, "False") === 0) { ?>selected<?php } ?>> Non</option>
	</select>
	<br/><label class="control-label" for="langage">Langue d'affichage</label>
	<select name="langage" class="ccm-input-select">		
		<option value="FR" <?php if (strcmp($langage, "FR") === 0) { ?>selected<?php } ?>> Français</option>
		<option value="EN" <?php if (strcmp($langage, "EN") === 0) { ?>selected<?php } ?>> Anglais</option>
	</select>
</div>
