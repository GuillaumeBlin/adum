<?php defined('C5_EXECUTE') or die(_("Access Denied."));

$parsing_types = array("doctors_of_the_year" => "Docteur.e.s d'une année", "phd_proposal" => "Propositions de sujets de thèses", "annu" => "Annuaire des doctorant.e.s d'une année", "phd_defense_by_ed" => "Soutenances à venir", "members_annu" => "Annuaire des encadrant.e.s", "training_by_ed" => "Formations à venir", "");
$codes = array(""=>"CED","41" => "ED Droit", "42" => "ED Entreprise Economie Société", "40" => "ED Sciences Chimiques", "154" => "ED Sciences de la Vie et de la Santé", "304" => "ED Sciences et environnements", "209" => "ED Sciences Physiques et de l'Ingénieur", "545" => "ED Sociétés, Politique, Santé Publique", "39" => "ED Mathématiques et Informatique");
$year_needed = array("doctors_of_the_year", "annu");
if(empty($parsing)) $parsing=null;
if(empty($filter)) $filter=null;
if(empty($hdr_only)) $hdr_only='All';
if(empty($active_only)) $active_only='True';
if(empty($year)) $year=date("Y");
if(empty($details)) $details='True';
if(empty($langage)) $langage='FR';

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
	<br/><label class="control-label" for="hdr_adt_only">ADT/HDR seulement</label>
	<select id="hdr_adt_only" name="hdr_adt_only" class="ccm-input-select">		
		<option value="All" <?php if (strcmp($hdr_only, "All") === 0) { ?>selected<?php } ?>> Toutes et tous</option>
		<option value="HDR_ADT" <?php if (strcmp($hdr_only, "HDR_ADT") === 0) { ?>selected<?php } ?>> Seulement les HDRs et ADTs</option>
		<option value="HDR" <?php if (strcmp($hdr_only, "HDR") === 0) { ?>selected<?php } ?>> Seulement les HDRs</option>
		<option value="ADT" <?php if (strcmp($hdr_only, "ADT") === 0) { ?>selected<?php } ?>> Seulement les ADTs</option>
	</select>
	<br/><label class="control-label" for="active_only">Collègues actives et actifs seulement</label>
	<select id="adt_only" name="active_only" class="ccm-input-select">		
		<option value="True" <?php if (strcmp($active_only, "True") === 0) { ?>selected<?php } ?>> Oui</option>
		<option value="False" <?php if (strcmp($active_only, "False") === 0) { ?>selected<?php } ?>> Non</option>
	</select>

	<br/><label class="control-label" for="year">Année affichée</label>
	<select id="year" name="year" class="ccm-input-select">
		<option value="current" <?php if (strcmp($year, $i) === 0) { ?>selected<?php } ?>>current</option>	
	<?php for($i=2016;$i<date("Y")+1;$i++) { ?>
			<option value="<?php echo $i; ?>" <?php if (strcmp($year, $i) === 0) { ?>selected<?php } ?>> <?php echo $i; ?> - <?php echo $i+1; ?></option>
		<?php } ?>
	</select>
	<br/>
	<label class="control-label" for="details">Présentation détaillée</label>
	<select id="details" name="details" class="ccm-input-select">		
		<option value="True" <?php if (strcmp($details, "True") === 0) { ?>selected<?php } ?>> Oui</option>
		<option value="False" <?php if (strcmp($details, "False") === 0) { ?>selected<?php } ?>> Non</option>
	</select>
	<br/><label class="control-label" for="langage">Langue d'affichage</label>
	<select id="langage" name="langage" class="ccm-input-select">		
		<option value="FR" <?php if (strcmp($langage, "FR") === 0) { ?>selected<?php } ?>> Français</option>
		<option value="EN" <?php if (strcmp($langage, "EN") === 0) { ?>selected<?php } ?>> Anglais</option>
	</select>
</div>

<script>
$("#parsing").change(function() {
    $('#filter option[value="-1"]').prop("selected", true);
	var disabled = (this.value != "doctors_of_the_year" && this.value != "annu");
    	$("#year").prop("disabled", disabled);
		$('#filter option[value=""]').prop("disabled", true);
		if (this.value=="training_by_ed"){
			$('#filter option[value=""]').prop("disabled", false);
		}
});

//$('#filter option[value="-1"]').prop("selected", true);
var disabled = ("<?php echo $parsing;?>" != "doctors_of_the_year" && "<?php echo $parsing;?>" != "annu");
$("#year").prop("disabled", disabled);
$('#filter option[value=""]').prop("disabled", true);
if ("<?php echo $parsing;?>"=="training_by_ed"){
	$('#filter option[value=""]').prop("disabled", false);
}
</script>