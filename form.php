<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$parsing_types = array("doctors_of_the_year" => "Docteur.e.s d'une année", "phd_proposal" => "Propositions de sujets de thèses", "annu" => "Annuaire des doctorant.e.s d'une année", "phd_defense_by_ed" => "Soutenances à venir", "members_annu" => "Annuaire des encadrant.e.s", "training_by_ed" => "Formations");
$codes = array(""=>"CED","41" => "ED Droit", "42" => "ED Entreprise Economie Société", "40" => "ED Sciences Chimiques", "154" => "ED Sciences de la Vie et de la Santé", "304" => "ED Sciences et environnements", "209" => "ED Sciences Physiques et de l'Ingénieur", "545" => "ED Sociétés, Politique, Santé Publique", "39" => "ED Mathématiques et Informatique");
$year_needed = array("doctors_of_the_year", "annu");
?>


<div class="form-group">
	<label class="control-label" for="parsing">Type d'information</label>
	<select id="parsing" name="parsing" class="ccm-input-select">
		<?php foreach ($parsing_types as $key => $opt) { ?>
			<option value="<?php echo $key; ?>" <?php if (strcmp($parsing, $key) === 0) { ?>selected<?php } ?>> <?php echo $opt; ?></option>
		<?php } ?>
	</select>
<br/>
	<label class="control-label" for="filter">Filtre <sup class="fas fa-asterisk"></sup></label>
	<select id="filter" name="filter" class="ccm-input-select">
		<option value="-1">Aucun</option> 
		<?php foreach ($codes as $key => $opt) { ?>
			<option value="<?php echo $key; ?>" <?php if (strcmp($filter, $key) === 0) { ?>selected<?php } ?>> <?php echo $opt; ?></option>
		<?php } ?>
	</select>
	<br/><label class="control-label" for="year">Année affichée</label>
	<select id="year" name="year" class="ccm-input-select">
		<?php for($i=2016;$i<date("Y")+1;$i++) { ?>
			<option value="<?php echo $i; ?>" <?php if (strcmp($year, $i) === 0) { ?>selected<?php } ?>> <?php echo $i; ?> - <?php echo $i+1; ?></option>
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

<script>
	function updateForm(){
		var disabled = ($("#parsing option: selected").value != "doctors_of_the_year" && $("#parsing option: selected").value != "annu");
    	$("#year").prop("disabled", disabled);
		$('#filter option[value=""]').prop("disabled", true);
		if ($("#parsing option: selected").value=="training_by_ed"){
			$('#filter option[value=""]').prop("disabled", false);
		}
	}
$("#parsing").change(function() {
    $('#filter option[value="-1"]').prop("selected", true);
	updateForm();
});
updateForm();
</script>
