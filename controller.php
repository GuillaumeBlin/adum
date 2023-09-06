<?php

declare(strict_types=1);

namespace Application\Block\Adum;

use DOMDocument;
use Concrete\Core\Block\BlockController;
use Concrete\Core\File\Filesystem;

class Controller extends BlockController
{

    protected $btTable = "btAdum";
    protected $btInterfaceWidth = "350";
    protected $btInterfaceHeight = "240";
    protected $btDefaultSet = 'basic';
    protected $jsonFiles = array(
        "inscrits"  => "/../../files/datas_adum/ubx_inscrits.json",
        "formations"  => "/../../files/datas_adum/ubx_formations.json",
        "responsables" => "/../../files/datas_adum/ubx_responsables.json",
        "sujets" => "/../../files/datas_adum/ubx_sujets.json",
        "structures"   => "/../../files/datas_adum/ubx_structures.json"
    );
    protected $codes = array(""=>"Collège des Ecoles Doctorales","41" => "ED Droit", "42" => "ED Entreprise Economie Société", "40" => "ED Sciences Chimiques", "154" => "ED Sciences de la Vie et de la Santé", "304" => "ED Sciences et environnements", "209" => "ED Sciences Physiques et de l'Ingénieur", "545" => "ED Sociétés, Politique, Santé Publique", "39" => "ED Mathématiques et Informatique");

    public function getBlockTypeName(): string
    {
        return 'ADUM';
    }

    public function getBlockTypeDescription(): string
    {
        return t('A simple block gathering ADUM public information');
    }

    public function validate($args)
    {
        $error = parent::validate($args);
        if (!is_array($args)) {
            $error->add(t('Invalid data type, data must be an array.'));
            return $error;
        }

        $parsing = $args['parsing'] ?? null;
        if (!$parsing) {
            $error->add(t('You must select a parsing option.'));
        }

        return $error;
    }

    public function save($args): void
    {
        parent::save($args);
    }

    /* TOOLBOX functions */

    private function retrieve_json($type, $year)
    {
        if ($type == "inscrits") {
            return json_decode(file_get_contents(realpath(dirname(__FILE__)) . str_replace(".json", "_" . $year . ".json", $this->jsonFiles[$type])), true);
        }

        return json_decode(file_get_contents(realpath(dirname(__FILE__)) . $this->jsonFiles[$type]), true);
    }

    
    private function array_except($array, $keys)
    {
        return array_diff_key($array, array_flip((array) $keys));
    }

    private function array_extract($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    private function group_by($key, $data)
    {
        $result = array();
        foreach ($data as $val) {
            if (array_key_exists($key, $val)) {
                $result[$val[$key]][] = $this->array_except($val, $key);
            } else {
                $result[""][] = $this->array_except($val, $key);
            }
        }
        return $result;
    }

    /* DISPLAY functions */

    private function display_training($modT) {
        echo "<li><a href='https://adum.fr/script/formations.pl?mod=".$modT['mod']."&site=UBX'>".$modT['libelle']."</a> - ".$modT['date_debut']."</li>";
    }

    private function display_member_annu($member)
    {        
        echo "<li>";
        echo '<a target="_blank" href="https://adum.fr/as/ed/detailResp.pl?resp=' . $member["matricule"] . '">' . $member["prenom"] . ' ' . $member["nom"] . '</a> ';
        echo "</li>";
    }

    private function display_laboratory($mat, $lab)
    {
        return '<a target="_blank" href="https://adum.fr/as/ed/fiche.pl?mat=' . $mat . '">' . $lab[0]["libelle"] . '</a> ';
    }

    private function display_annu($defense)
    {
        $year = (int)$defense["niveau_Etud"][0];
        switch ($year) {
            case 1:
                if (strcmp($this->langage, "FR") == 0) {
                    $year = $year . "ère";
                } else {
                    $year = $year . "st";
                }

                break;
            case 2:
                if (strcmp($this->langage, "FR") == 0) {
                    $year = $year . "nde";
                } else {
                    $year = $year . "nd";
                }
                break;
            case 3:
                if (strcmp($this->langage, "FR") == 0) {
                    $year = $year . "ème";
                } else {
                    $year = $year . "rd";
                }

                break;
            default:
                if (strcmp($this->langage, "FR") == 0) {
                    $year = $year . "ème";
                } else {
                    $year = $year . "th";
                }

                break;
        }
        echo "<li>";
        if (strcmp($this->langage, "FR") == 0) {
            echo '<a target="_blank" href="https://adum.fr/script/cv.pl?site=CDUBX&matri=' . $defense["Matricule_etudiant"] . '">' . $defense["prenom"] . ' ' . $defense["nom"] . '</a> ';
            echo  ' (' . $year . ' année) - ';
            echo $defense["these_titre"] . " - ";
            //echo " (".$defense["these_laboratoire"].") ";        
            echo   'sous la direction de ' . $defense["these_directeur_these_prenom"] . " " . $defense["these_directeur_these_nom"];
            if ($defense["these_codirecteur_these_nom"] != "") {
                echo ' et ' . $defense["these_codirecteur_these_prenom"] . " " . $defense["these_codirecteur_these_nom"];
            }
        } else {
            echo '<a target="_blank" href="https://adum.fr/script/cv.pl?site=CDUBX&matri=' . $defense["Matricule_etudiant"] . '">' . $defense["prenom"] . ' ' . $defense["nom"] . '</a> ';
            echo  ' (' . $year . ' year) - ';
            echo $defense["these_titre_anglais"] . " - ";
            //echo " (".$defense["these_laboratoire"].") ";        
            echo  'under the supervision of ' . $defense["these_directeur_these_prenom"] . " " . $defense["these_directeur_these_nom"];
            if ($defense["these_codirecteur_these_nom"] != "") {
                echo ' and ' . $defense["these_codirecteur_these_prenom"] . " " . $defense["these_codirecteur_these_nom"];
            }
        }

        echo "</li>";
    }

    private function display_proposition($prop)
    {
        echo "<li>";
        if (strcmp($this->langage, "FR") == 0) {
            echo '<a target="_blank" href="https://adum.fr/as/ed/voirproposition.pl?site=CDUBX&matricule_prop=' . $prop["matricule_prop"] . '">' . $prop["sujet"] . '</a>';
        } else {
            echo '<a target="_blank" href="https://adum.fr/as/ed/voirproposition.pl?site=CDUBX&matricule_prop=' . $prop["matricule_prop"] . '">' . $prop["sujet_gb"] . '</a>';
        }
        echo "</li>";
    }

    private function display_defense($defense)
    {

        echo "<li>";
        if (strcmp($this->langage, "FR") == 0) {
            echo '<a target="_blank" href="https://adum.fr/script/detailSout.pl?site=CDUBX&&langue=fr&mat=' . $defense["Matricule_etudiant"] . '">' . $defense["these_titre"] . '</a> ';
            echo "par ";
            echo '<a target="_blank" href="https://adum.fr/script/cv.pl?site=CDUBX&matri=' . $defense["Matricule_etudiant"] . '">' . $defense["prenom"] . ' ' . $defense["nom"] . '</a> ';
            echo " (" . $defense["these_laboratoire"] . ") ";
            echo  'soutenue le ', $defense["these_date_soutenance"];
            echo   ' sous la direction de ' . $defense["these_directeur_these_prenom"] . " " . $defense["these_directeur_these_nom"];
            if ($defense["these_codirecteur_these_nom"] != "") {
                echo ' et ' . $defense["these_codirecteur_these_prenom"] . " " . $defense["these_codirecteur_these_nom"];
            }
        } else {
            echo '<a target="_blank" href="https://adum.fr/script/detailSout.pl?site=CDUBX&&langue=fr&mat=' . $defense["Matricule_etudiant"] . '">' . $defense["these_titre_anglais"] . '</a> ';
            echo 'by ';
            echo '<a target="_blank" href="https://adum.fr/script/cv.pl?site=CDUBX&matri=' . $defense["Matricule_etudiant"] . '">' . $defense["prenom"] . ' ' . $defense["nom"] . '</a> ';
            echo " (" . $defense["these_laboratoire"] . ") ";
            echo  'defended on ', $defense["these_date_soutenance"];
            echo  ' under the supervision of ' . $defense["these_directeur_these_prenom"] . " " . $defense["these_directeur_these_nom"];
            if ($defense["these_codirecteur_these_nom"] != "") {
                echo ' and ' . $defense["these_codirecteur_these_prenom"] . " " . $defense["these_codirecteur_these_nom"];
            }
        }
        echo "</li>";
    }

    private function display_defense_to_come($defense)
    {

        echo "<li>";
        if (strcmp($this->langage, "FR") == 0) {
            echo '<a target="_blank" href="https://adum.fr/script/detailSout.pl?site=CDUBX&&langue=fr&mat=' . $defense["Matricule_etudiant"] . '">' . $defense["these_titre"] . '</a> ';
            echo "par ";
            echo '<a target="_blank" href="https://adum.fr/script/cv.pl?site=CDUBX&matri=' . $defense["Matricule_etudiant"] . '">' . $defense["prenom"] . ' ' . $defense["nom"] . '</a> ';
            echo " (" . $defense["these_laboratoire"] . ") ";
            echo  'à soutenir le ', $defense["these_date_soutenance"];
            echo   ' sous la direction de ' . $defense["these_directeur_these_prenom"] . " " . $defense["these_directeur_these_nom"];
            if ($defense["these_codirecteur_these_nom"] != "") {
                echo ' et ' . $defense["these_codirecteur_these_prenom"] . " " . $defense["these_codirecteur_these_nom"];
            }
        } else {
            echo '<a target="_blank" href="https://adum.fr/script/detailSout.pl?site=CDUBX&&langue=fr&mat=' . $defense["Matricule_etudiant"] . '">' . $defense["these_titre_anglais"] . '</a> ';
            echo 'by ';
            echo '<a target="_blank" href="https://adum.fr/script/cv.pl?site=CDUBX&matri=' . $defense["Matricule_etudiant"] . '">' . $defense["prenom"] . ' ' . $defense["nom"] . '</a> ';
            echo " (" . $defense["these_laboratoire"] . ") ";
            echo  'to be defend on ', $defense["these_date_soutenance"];
            echo   ' under the supervision of ' . $defense["these_directeur_these_prenom"] . " " . $defense["these_directeur_these_nom"];
            if ($defense["these_codirecteur_these_nom"] != "") {
                echo ' and ' . $defense["these_codirecteur_these_prenom"] . " " . $defense["these_codirecteur_these_nom"];
            }
        }
        echo "</li>";
    }

    /* SORTING functions */

    private function proposals_sorter(array $a, array $b)
    {
        return [$a['these_ED_code'], $a['Struct_libelle'], $a['specialite'], $a['sujet']] <=> [$b['these_ED_code'], $b['Struct_libelle'], $b['specialite'], $b['sujet']];
    }

    private function students_sorter(array $a, array $b)
    {
        return [$a['these_ED_code'], $a['these_specialite'], $a['nom']] <=> [$b['these_ED_code'], $b['these_specialite'], $b['nom']];
    }

    private function members_sorter(array $a, array $b)
    {
        return [$a['ED_code'], $a['nom'], $a['prenom']] <=> [$b['ED_code'], $b['nom'], $b['prenom']];
    }

    private function trainings_sorter(array $a, array $b)
    {
        return [$a['ED_code'], $a['categorie'], $a['mod']] <=> [$b['ED_code'], $b['categorie'], $b['mod']];
    }

    /* LOADING functions */

    private function load_phd_proposal()
    {

        $proposals = $this->retrieve_json("sujets", $this->year);
        $props = $proposals["data"][0]["Proposition_de_these"];
        array_shift($props);
        $result = array();
        foreach ($props as &$value) {
            $prop = $value["Proposition"];
            $prop = $this->array_extract($prop, ['Struct_libelle', 'specialite', 'sujet', 'sujet_gb', 'matricule_prop', 'these_ED_code']);
            array_push($result, $prop);
        }
        usort($result, array($this, 'proposals_sorter'));
        $byGroup = $this->group_by("these_ED_code", $result);
        foreach ($byGroup as &$valueByED) {
            $valueByED = $this->group_by("Struct_libelle", $valueByED);
            foreach ($valueByED as &$valueByLab) {
                $valueByLab = $this->group_by("specialite", $valueByLab);
            }
        }
        //echo "<pre>" . var_export($byGroup, true) . "</pre>";
        if ($this->filter != "-1" && !array_key_exists($this->filter, $byGroup)) {
            if (strcmp($this->langage, "FR") == 0) {
                echo "Aucune proposition de sujet pour cette école doctorale.";
            } else {
                echo "No PhD proposal for this doctoral school.";
            }
        } else {
            foreach ($byGroup as $keyByED => $valueByED) {
                if ($this->filter == "-1") {
                    echo "<h3>" . $this->codes[$keyByED] . "</h3>";
                } else {
                    if ($keyByED != $this->filter) {
                        continue;
                    }
                }
                foreach ($valueByED as $keyByLab => $valueByLab) {
                    if ($this->filter != "-1") {
                        echo "<h3>" . $keyByLab . "</h3>";
                    } else {
                        echo "<h4>" . $keyByLab . "</h4>";
                    }
                    $datas = array();


                    foreach ($valueByLab as $keyBySpeciality => $valueBySpeciality) {
                        $i = count($valueBySpeciality);
                        if ($i > 1) {
                            if (strcmp($this->langage, "FR") == 0) {
                                $datas["Sujets en " . $keyBySpeciality] = $i;
                            } else {
                                $datas["Subjects in " . $keyBySpeciality] = $i;
                            }
                        } else {
                            if (strcmp($this->langage, "FR") == 0) {
                                $datas["Sujet en " . $keyBySpeciality] = $i;
                            } else {
                                $datas["Subject in " . $keyBySpeciality] = $i;
                            }
                        }
                    }
                    $this->show_key_numbers($datas);
                    if (strcmp($this->details, "True") == 0) {
                        foreach ($valueByLab as $keyBySpeciality => $valueBySpeciality) {
                            if ($this->filter != "-1") {
                                echo "<h4>" . $keyBySpeciality . "</h4>";
                            } else {
                                echo "<h5>" . $keyBySpeciality . "</h5>";
                            }
                            echo "<ul>";
                            foreach ($valueBySpeciality as $prop) {
                                $this->display_proposition($prop);
                            }
                            echo "</ul>";
                        }
                    }
                }
            }
        }
    }

    /*private function load_phd_proposal_mod($filter)
    {
        if (!$filter) {
            echo "Missing filter";
            return;
        }
        $url = "https://adum.fr/as/ed/candidatureED.pl?mat=" . $filter . "&sec=mod";
        $dom = new DOMDocument();
        $html = $this->retrieve_html($url, null);

        @$dom->loadHTML($html);
        $adiv = $dom->getElementById('mainform')->getElementsByTagName("div")[0];
        $adiv->removeChild($adiv->firstChild);
        $adiv->removeChild($adiv->firstChild);
        $adiv->removeChild($adiv->firstChild);
        $adiv->removeChild($adiv->firstChild);
        $adiv->removeChild($adiv->firstChild);
        $adiv->removeChild($adiv->firstChild);
        $adiv->removeChild($adiv->firstChild);
        echo $this->get_inner_html($adiv);
    }*/

    private function show_key_numbers($datas)
    {
        $cpt = 0;
        foreach ($datas as $key => $value) {
            if ($cpt % 4 == 0) {
                echo '<div class="home-key-numbers-inner">';
                echo '<ul>';
            }
            $cpt = $cpt + 1;
            echo '<li>';
            echo '<div class="home-key-numbers-item">';
            echo '<strong class="home-key-numbers-item-number countup" data-value="' . $value . '">' . $value . '</strong>';
            echo '<p class="home-key-numbers-item-title">' . $key . '</p>';
            echo '<p class="home-key-numbers-item-subtitle"></p>';
            echo '</div>';
            echo '</li>';
            if ($cpt % 4 == 0) {
                echo '</ul></div>';
            }
        }
        if ($cpt % 4 != 0) {
            echo '</ul></div>';
        }
        echo '<script>';
        echo 'jQuery(function($) {';
        echo "var \$countup = \$('.countup');\n";
        echo "\$.when('countup', function() {\n";
        echo 'var processItem = function(elt) {';
        echo 'var $this = $(elt),';
        echo "countUp = new CountUp(elt, parseInt(\$this.attr('data-value')), {\n";
        echo 'duration: 1.5,';
        echo "separator: ' '\n";
        echo '});';
        echo 'if (!countUp.error) {';
        echo 'countUp.start();';
        echo '}';
        echo '};';
        echo "if (!('IntersectionObserver' in window)) {\n";
        echo '$countup.each(function() {';
        echo 'processItem(this);';
        echo '});';
        echo '} else {';
        echo 'var observer = new IntersectionObserver(function(entries) {';
        echo 'entries.forEach(function(entry) {';
        echo 'if (entry.isIntersecting) {';
        echo 'if (observer) {';
        echo 'observer.unobserve(entry.target);';
        echo '}';
        echo 'processItem(entry.target);';
        echo '}';
        echo '});';
        echo '}, {';
        echo "rootMargin: '10px 0px',\n";
        echo 'threshold: 0.01';
        echo '});';
        echo '$countup.each(function() {';
        echo 'observer.observe(this);';
        echo '});';
        echo '}';
        echo '});';
        echo '});';
        echo '</script>';
    }

    /*Doctors of the current academic year */

    private function load_doctors_of_the_year()
    {
        $students = $this->retrieve_json("inscrits", $this->year);

        $students = $students["data"][0];
        foreach ($students as &$value) {
            $value = $this->array_extract($value, [
                "Matricule_etudiant",
                "nom",
                "prenom",
                "these_ED_code",
                "these_codirecteur_these_nom",
                "these_codirecteur_these_prenom",
                "these_date_soutenance",
                "these_directeur_these_nom",
                "these_directeur_these_prenom",
                "these_laboratoire",
                "these_specialite",
                "these_titre",
                "these_titre_anglais"
            ]);
        }

        usort($students, array($this, 'students_sorter'));
        $students = array_filter($students, function ($student) {
            return $student["these_date_soutenance"] != "" && date_parse($student["these_date_soutenance"])["year"] == $this->year &&  time() > strtotime($student["these_date_soutenance"]);
        });

        $byGroup = $this->group_by("these_ED_code", $students);
        foreach ($byGroup as &$valueByED) {
            $valueByED = $this->group_by("these_specialite", $valueByED);
        }
        //echo "<pre>" . var_export($byGroup, true) . "</pre>";

        if ($this->filter != "-1" && !array_key_exists($this->filter, $byGroup)) {
            if (strcmp($this->langage, "FR") == 0) {
                echo "Pas de docteur.e. encore cette année pour cette école doctorale.";
            } else {
                echo "No doctors yet this year for this doctoral school.";
            }
        } else {

            foreach ($byGroup as $keyByED => $valueByED) {
                if ($this->filter == "-1") {
                    echo "<h3>" . $this->codes[$keyByED] . "</h3>";
                } else {
                    if ($keyByED != $this->filter) {
                        continue;
                    }
                }
                $datas = array();
                foreach ($valueByED as $keyBySpeciality => $valueBySpeciality) {
                    $i = count($valueBySpeciality);
                    if ($i > 1) {
                        if (strcmp($this->langage, "FR") == 0) {
                            $datas["Docteur.e.s en " . $keyBySpeciality] = $i;
                        } else {
                            $datas["Doctors in " . $keyBySpeciality] = $i;
                        }
                    } else {
                        if (strcmp($this->langage, "FR") == 0) {
                            $datas["Docteur.e en " . $keyBySpeciality] = $i;
                        } else {
                            $datas["Doctor in " . $keyBySpeciality] = $i;
                        }
                    }
                }
                $this->show_key_numbers($datas);
                if (strcmp($this->details, "True") == 0) {
                    foreach ($valueByED as $keyBySpeciality => $valueBySpeciality) {

                        if ($this->filter != "-1") {
                            echo "<h3>" . $keyBySpeciality . "</h3>";
                        } else {
                            echo "<h4>" . $keyBySpeciality . "</h4>";
                        }
                        echo "<ul>";
                        foreach ($valueBySpeciality as $student) {
                            $this->display_defense($student);
                        }
                        echo "</ul>";
                    }
                }
            }
        }
    }


    /*ED members*/
    private function load_members_annu()
    {
        $members = $this->retrieve_json("responsables", $this->year);
        $members = $members["data"];

        $structures = $this->retrieve_json("structures", $this->year);
        $structures = $structures["data"];
        $structuresbyGroup = $this->group_by("matricule", $structures);

        $structuresbyGroup[0] = array();
        $structuresbyGroup[0][0] = array();
        $structuresbyGroup[0][0]["libelle"] = "Laboratoire inconnu";

        $nmembers=array();
        foreach ($members as &$value) {
            if (!array_key_exists($value["matricule_structure"], $structuresbyGroup)) {
                $value["matricule_structure"] = 0;
            }
            $i = count($value["ED_code"]);
            if ($i == 0) {
                unset($value);
                continue;
            }
            $eds = array_replace([], $value["ED_code"]);
            foreach ($eds as $ed) {
                $value["ED_code"] = $ed;
                array_push($nmembers, $value);
            }                        
        }
        usort($nmembers, array($this, 'members_sorter'));

        $membersbyGroup = $this->group_by("ED_code", $nmembers);
        foreach ($membersbyGroup as &$valueByED) {
            $valueByED = $this->group_by("matricule_structure", $valueByED);
        }


        //         echo "<pre>" . var_export($membersbyGroup, true) . "</pre>";

        if ($this->filter != "-1" && !array_key_exists($this->filter, $membersbyGroup)) {
            if (strcmp($this->langage, "FR") == 0) {
                echo "Aucun.e encadrant.e inscrit.e dans cette école doctorale.";
            } else {
                echo "No PhD supervisor registered to this doctoral school.";
            }
        } else {
            foreach ($membersbyGroup as $keyByED => $valueByED) {
                if ($keyByED == "") {
                    continue;
                }
                if ($this->filter == "-1") {
                    echo "<h3>" . $this->codes[$keyByED] . "</h3>";
                } else {
                    if ($keyByED != $this->filter) {
                        continue;
                    }
                }
                $datas = array();
                foreach ($valueByED as $keyByStructure => $valueByStructure) {
                    $i = count($valueByStructure);
                    if ($i > 1) {
                        if (strcmp($this->langage, "FR") == 0) {
                            $datas["Encadrant.e.s - " . $structuresbyGroup[$keyByStructure][0]["libelle"]] = $i;
                        } else {
                            $datas["PhD supervisors - " . $structuresbyGroup[$keyByStructure][0]["libelle"]] = $i;
                        }
                    } else {
                        if (strcmp($this->langage, "FR") == 0) {
                            $datas["Encadrant.e - " . $structuresbyGroup[$keyByStructure][0]["libelle"]] = $i;
                        } else {
                            $datas["PhD supervisor - " . $structuresbyGroup[$keyByStructure][0]["libelle"]] = $i;
                        }
                    }
                }
                $this->show_key_numbers($datas);
                if (strcmp($this->details, "True") == 0) {
                    foreach ($valueByED as $keyByStructure => $valueByStructure) {
                        if ($this->filter != "-1") {
                            echo "<h3>" . $this->display_laboratory($keyByStructure, $structuresbyGroup[$keyByStructure]) . "</h3>";
                        } else {
                            echo "<h4>" . $this->display_laboratory($keyByStructure, $structuresbyGroup[$keyByStructure]) . "</h4>";
                        }
                        echo '<ul class="card-columns" style="column-count: 3;">';
                        foreach ($valueByStructure as $member) {
                            $this->display_member_annu($member);
                        }
                        echo "</ul>";
                    }
                }
            }
        }
    }


    /*Phd students*/
    private function load_annu()
    {
        $students = $this->retrieve_json("inscrits", $this->year);

        $students = $students["data"][0];
        foreach ($students as &$value) {
            $value = $this->array_extract($value, [
                "Matricule_etudiant",
                "nom",
                "prenom",
                "niveau_Etud",
                "these_ED_code",
                "these_codirecteur_these_nom",
                "these_codirecteur_these_prenom",
                "these_date_soutenance",
                "these_directeur_these_nom",
                "these_directeur_these_prenom",
                "these_laboratoire",
                "these_specialite",
                "these_titre",
                "these_titre_anglais"
            ]);
        }

        usort($students, array($this, 'students_sorter'));

        $byGroup = $this->group_by("these_ED_code", $students);
        foreach ($byGroup as &$valueByED) {
            $valueByED = $this->group_by("these_specialite", $valueByED);
        }
        //echo "<pre>" . var_export($byGroup, true) . "</pre>";

        if ($this->filter != "-1" && !array_key_exists($this->filter, $byGroup)) {
            if (strcmp($this->langage, "FR") == 0) {
                echo "Aucun.e étudiant.e inscrit.e dans cette école doctorale.";
            } else {
                echo "No PhD students registered to this doctoral school.";
            }
        } else {
            foreach ($byGroup as $keyByED => $valueByED) {
                if ($this->filter == "-1") {
                    echo "<h3>" . $this->codes[$keyByED] . "</h3>";
                } else {
                    if ($keyByED != $this->filter) {
                        continue;
                    }
                }
                $datas = array();
                foreach ($valueByED as $keyBySpeciality => $valueBySpeciality) {
                    $i = count($valueBySpeciality);
                    if ($i > 1) {
                        if (strcmp($this->langage, "FR") == 0) {
                            $datas["Doctorant.e.s en " . $keyBySpeciality] = $i;
                        } else {
                            $datas["PhD students in " . $keyBySpeciality] = $i;
                        }
                    } else {
                        if (strcmp($this->langage, "FR") == 0) {
                            $datas["Doctorant.e en " . $keyBySpeciality] = $i;
                        } else {
                            $datas["PhD student in " . $keyBySpeciality] = $i;
                        }
                    }
                }
                $this->show_key_numbers($datas);
                if (strcmp($this->details, "True") == 0) {
                    foreach ($valueByED as $keyBySpeciality => $valueBySpeciality) {
                        if ($this->filter != "-1") {
                            echo "<h3>" . $keyBySpeciality . "</h3>";
                        } else {
                            echo "<h4>" . $keyBySpeciality . "</h4>";
                        }
                        echo "<ul>";
                        foreach ($valueBySpeciality as $student) {
                            $this->display_annu($student);
                        }
                        echo "</ul>";
                    }
                }
            }
        }
    }



    /*Incoming defense*/
    private function load_phd_defense_by_ed()
    {

        

        $students = $this->retrieve_json("inscrits", $this->year);

        $students = $students["data"][0];
        foreach ($students as &$value) {
            $value = $this->array_extract($value, [
                "Matricule_etudiant",
                "nom",
                "prenom",
                "these_ED_code",
                "these_codirecteur_these_nom",
                "these_codirecteur_these_prenom",
                "these_date_soutenance",
                "these_directeur_these_nom",
                "these_directeur_these_prenom",
                "these_laboratoire",
                "these_specialite",
                "these_titre",
                "these_titre_anglais"
            ]);
        }

        usort($students, array($this, 'students_sorter'));
        $students = array_filter($students, function ($student) {
            return $student["these_date_soutenance"] != "" && date_parse($student["these_date_soutenance"])["year"] == date("Y") && time() < strtotime($student["these_date_soutenance"]);
        });

        $byGroup = $this->group_by("these_ED_code", $students);
        foreach ($byGroup as &$valueByED) {
            $valueByED = $this->group_by("these_specialite", $valueByED);
        }
        //echo "<pre>" . var_export($byGroup, true) . "</pre>";

        if ($this->filter != "-1" && !array_key_exists($this->filter, $byGroup)) {
            if (strcmp($this->langage, "FR") == 0) {
                echo "Pas de soutenance à venir pour cette école doctorale.";
            } else {
                echo "No PhD defense upcoming for this doctoral school.";
            }
        } else {
            foreach ($byGroup as $keyByED => $valueByED) {
                if ($this->filter == "-1") {
                    echo "<h3>" . $this->codes[$keyByED] . "</h3>";
                } else {
                    if ($keyByED != $this->filter) {
                        continue;
                    }
                }
                $datas = array();
                foreach ($valueByED as $keyBySpeciality => $valueBySpeciality) {
                    $i = count($valueBySpeciality);
                    if ($i > 1) {
                        if (strcmp($this->langage, "FR") == 0) {
                            $datas["Soutenances en " . $keyBySpeciality] = $i;
                        } else {
                            $datas["PhD defenses in " . $keyBySpeciality] = $i;
                        }
                    } else {
                        if (strcmp($this->langage, "FR") == 0) {
                            $datas["Soutenance en " . $keyBySpeciality] = $i;
                        } else {
                            $datas["PhD defense in " . $keyBySpeciality] = $i;
                        }
                    }
                }
                $this->show_key_numbers($datas);
                if (strcmp($this->details, "True") == 0) {
                    foreach ($valueByED as $keyBySpeciality => $valueBySpeciality) {
                        if ($this->filter != "-1") {
                            echo "<h3>" . $keyBySpeciality . "</h3>";
                        } else {
                            echo "<h4>" . $keyBySpeciality . "</h4>";
                        }
                        echo "<ul>";
                        foreach ($valueBySpeciality as $student) {
                            $this->display_defense_to_come($student);
                        }
                        echo "</ul>";
                    }
                }
            }
        }
    }


    /*Incoming defense*/
    private function load_training_by_ed()
    {        
        $trainings = $this->retrieve_json("formations", $this->year);

        $ntrainings = $trainings["data"];

        usort($ntrainings, array($this, 'trainings_sorter'));

        $trainingsbyGroup = $this->group_by("ED_code", $ntrainings);
        foreach ($trainingsbyGroup as &$valueByED) {
            $valueByED = $this->group_by("categorie", $valueByED);
        }
        //echo "<pre>" . var_export($byGroup, true) . "</pre>";

        if ($this->filter != "-1" && !array_key_exists($this->filter, $trainingsbyGroup)) {
            if (strcmp($this->langage, "FR") == 0) {
                echo "Pas de formation pour cette école doctorale.";
            } else {
                echo "No training courses for this doctoral school.";
            }
        } else {
            foreach ($trainingsbyGroup as $keyByED => $valueByED) {
                if ($this->filter == "-1") {
                    echo "<h3>" . $this->codes[$keyByED] . "</h3>";
                } else {
                    if ($keyByED != $this->filter) {
                        continue;
                    }
                }
                /*$datas = array();
                foreach ($valueByED as $keyBySpeciality => $valueBySpeciality) {
                    $i = count($valueBySpeciality);
                    if ($i > 1) {
                        if (strcmp($this->langage, "FR") == 0) {
                            $datas["Soutenances en " . $keyBySpeciality] = $i;
                        } else {
                            $datas["PhD defenses in " . $keyBySpeciality] = $i;
                        }
                    } else {
                        if (strcmp($this->langage, "FR") == 0) {
                            $datas["Soutenance en " . $keyBySpeciality] = $i;
                        } else {
                            $datas["PhD defense in " . $keyBySpeciality] = $i;
                        }
                    }
                }
                $this->show_key_numbers($datas);*/
                if (strcmp($this->details, "True") == 0) {
                    foreach ($valueByED as $keyByCategory => $valueByCategory) {
                        if ($this->filter != "-1") {
                            echo "<h3>" . $keyByCategory . "</h3>";
                        } else {
                            echo "<h4>" . $keyByCategory . "</h4>";
                        }
                        echo "<ul>";
                        foreach ($valueByCategory as $training) {
                            $this->display_training($training);
                        }
                        echo "</ul>";
                    }
                }
            }
        }
    }

    public function action_load($bID = false)
    {
        if ($this->bID != $bID) {
            return false;
        }
        $this->{"load_" . $this->parsing}();
        exit;
    }
}
