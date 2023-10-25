<?php

use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Page\Page;

function array_except2($array, $keys)
{
    return array_diff_key($array, array_flip((array) $keys));
}

function defense_sorter(array $a, array $b)
{
    return [strtotime($a['these_date_soutenance']),$a['these_ED_code'], $a['these_specialite'],  $a['nom']] <=> [strtotime($b['these_date_soutenance']), $b['these_ED_code'], $b['these_specialite'], $b['nom']];
}

function array_extract($array, $keys)
{
    return array_intersect_key($array, array_flip((array) $keys));
}

function group_by($key, $data)
{
    $result = array();
    foreach ($data as $val) {
        if (array_key_exists($key, $val)) {
            $result[$val[$key]][] = array_except2($val, $key);
        } else {
            $result[""][] = array_except2($val, $key);
        }
    }
    return $result;
}

function addEvent($date, $desc, $place, $details, $lang)
    {
        if (strcmp($lang, "FR") == 0) {
            $parentPage =  Page::getByPath('/evenements');
        } else {
            $parentPage =  Page::getByPath('/en/events');
        }
        if (is_object($parentPage)) {
            $pageType = \PageType::getByHandle('evenement');
            $template = \PageTemplate::getByHandle('evenement');
            $url = 'sout_adum_' . $lang . '_' . $date;
            $sub_page_ids = $parentPage->getCollectionChildrenArray(1);
            foreach ($sub_page_ids as $id) {
                $page = \Page::getByID($id);
                if (str_starts_with($page->getCollectionName(), "Soutenances du " . $date)) {
                    $page->delete();
                }
                if (str_starts_with($page->getCollectionName(), "Phd defense on " . $date)) {
                    $page->delete();
                }
            }

            //champs obligatoires pour page
            $obligatoires_page = array(
                'cDescription' => $desc,
                'cHandle ' => $url,
            );
            if (strcmp($lang, "FR") == 0) {
                $obligatoires_page["cName"] = 'Soutenances du ' . $date;
            } else {
                $obligatoires_page["cName"] = 'Phd defense on ' . $date;
            }
            $page = $parentPage->add($pageType, $obligatoires_page, $template);
            // évènement pas dans le menu
            $page->setAttribute('exclude_nav', true);
            $page->setAttribute('date_debut',  $date . ' 00:00:00');
            $page->setAttribute('thumbnail', 174);
            $page->setAttribute('lieu',  $place);
            $page->setAttribute('date_fin',  $date . ' 23:59:59');
            
            $page->update(array(
                'cCacheFullPageContent' => false
            ));

            $block = BlockType::getByHandle('html');
            $data = array(
                'content' => $details
            );
            $page->addBlock($block, 'Main', $data);
        }
    }

    function get_display_defense_to_come($defense, $lang)
    {

        $res = "<li>";
        if (strcmp($lang, "FR") == 0) {
            $res .= '<h5>' . $defense["these_titre"] . '</h5> ';
            $res .= "<p>par ";
            $res .= '<a target="_blank" href="https://adum.fr/script/cv.pl?site=CDUBX&matri=' . $defense["Matricule_etudiant"] . '">' . $defense["prenom"] . ' ' . $defense["nom"] . '</a> ';
            $res .= " (" . $defense["these_laboratoire"] . ") </p>";
            $res .= "<p>Cette soutenance a lieu à " . $defense["these_heure_soutenance"] . " - " . $defense["these_soutenance_salle"] . " " . $defense["these_soutenance_adresse"] . "</p>";
            $res .= '<p>devant le jury composé de <ul>';
            foreach ($defense["soutenanceJury"] as $member) {
                $res .= "<li>" . $member["jury"]["prenom"] . " " . $member["jury"]["nom"] . " - " . $member["jury"]["grade"] . " - " . $member["jury"]["etab"] . " - " . $member["jury"]["qualite"] . "</li>";
            }
            $res .= "</ul></p>";
            $res .= '<p><a class="btn btn-primary" href="javascript:$(\'#collapse' . $defense["Matricule_etudiant"] . '\').toggle();" role="button" >Résumé</a></p><div id="collapse' . $defense["Matricule_etudiant"] . '" class="collapse block-verbatim"><div class="block-verbatim-inner"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" aria-hidden="true"><path d="M7 17.409c-0.003-0.091-0.005-0.198-0.005-0.306 0-3.683 2.248-6.841 5.447-8.177l0.059-0.022c1.105-0.664 0.5-1.501 0.5-1.501s-3.855-4.337-4.5-5.003c-0.49-0.506-0.88-0.534-1-0.5-0.279 0.066-7.5 1.449-7.5 11.006s11.088 16.544 12.5 17.009c0.67 0.222 1.414-0.668 1-1.001-0.62-0.446-6.5-4.91-6.5-11.506zM30.999 2.901h-12c-0.552 0-1 0.448-1 1 0 0 0 0.001 0 0.001v-0 7.504c0 0.553-1.606 13.036 6 18.51 0.131 0.12 0.307 0.193 0.5 0.193s0.369-0.073 0.501-0.194l-0.001 0.001c0.834-0.83 2.968-2.953 4.5-4.503 0.283-0.242 0.462-0.6 0.462-1s-0.178-0.758-0.46-0.998l-0.002-0.002c-2.036-1.889-3.35-4.53-3.499-7.479l-0.001-0.026c-0.199-4.69 0.32-4.502 1.13-4.502h3.87c0.552 0 1-0.448 1-1 0-0 0-0.001 0-0.001v0-6.503c0-0 0-0.001 0-0.001 0-0.552-0.448-1-1-1 0 0 0 0 0 0v0z"></path></svg><p>' . $defense["these_resume_fr"] . '</p></div></div>';
        } else {
            $res .= '<h5>' . $defense["these_titre_anglais"] . '</h5> ';
            $res .= "<p>by ";
            $res .= '<a target="_blank" href="https://adum.fr/script/cv.pl?site=CDUBX&matri=' . $defense["Matricule_etudiant"] . '">' . $defense["prenom"] . ' ' . $defense["nom"] . '</a> ';
            $res .= " (" . $defense["these_laboratoire"] . ") </p>";
            $res .= "<p>The defense will take place at " . $defense["these_heure_soutenance"] . " - " . $defense["these_soutenance_salle"] . " " . $defense["these_soutenance_adresse"] . "</p>";
            $res .= '<p>in front of the jury composed of <ul>';
            foreach ($defense["soutenanceJury"] as $member) {
                $res .= "<li>" . $member["jury"]["prenom"] . " " . $member["jury"]["nom"] . " - " . $member["jury"]["grade"] . " - " . $member["jury"]["etab"] . " - " . $member["jury"]["qualite"] . "</li>";
            }
            $res .= "</ul></p>";
            $res .= '<p><a class="btn btn-primary" href="javascript:$(\'#collapse' . $defense["Matricule_etudiant"] . '\').toggle();" role="button" >Summary</a></p><div id="collapse' . $defense["Matricule_etudiant"] . '" class="collapse block-verbatim"><div class="block-verbatim-inner"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" aria-hidden="true"><path d="M7 17.409c-0.003-0.091-0.005-0.198-0.005-0.306 0-3.683 2.248-6.841 5.447-8.177l0.059-0.022c1.105-0.664 0.5-1.501 0.5-1.501s-3.855-4.337-4.5-5.003c-0.49-0.506-0.88-0.534-1-0.5-0.279 0.066-7.5 1.449-7.5 11.006s11.088 16.544 12.5 17.009c0.67 0.222 1.414-0.668 1-1.001-0.62-0.446-6.5-4.91-6.5-11.506zM30.999 2.901h-12c-0.552 0-1 0.448-1 1 0 0 0 0.001 0 0.001v-0 7.504c0 0.553-1.606 13.036 6 18.51 0.131 0.12 0.307 0.193 0.5 0.193s0.369-0.073 0.501-0.194l-0.001 0.001c0.834-0.83 2.968-2.953 4.5-4.503 0.283-0.242 0.462-0.6 0.462-1s-0.178-0.758-0.46-0.998l-0.002-0.002c-2.036-1.889-3.35-4.53-3.499-7.479l-0.001-0.026c-0.199-4.69 0.32-4.502 1.13-4.502h3.87c0.552 0 1-0.448 1-1 0-0 0-0.001 0-0.001v0-6.503c0-0 0-0.001 0-0.001 0-0.552-0.448-1-1-1 0 0 0 0 0 0v0z"></path></svg><p>' . $defense["these_resume_anglais"] . '</p></div></div>';
        }
        $res .= "</li>";
        return $res;
    }

    function get_show_key_numbers($datas)
    {
        $cpt = 0;
        $res = "";
        foreach ($datas as $key => $value) {
            $res .= $value . " " . $key . ' - ';
        }
        return substr($res, 0, -3);
    }


    function update_events()
    {
       
        $codes = array("" => "Collège des Ecoles Doctorales", "41" => "ED Droit", "42" => "ED Entreprise Economie Société", "40" => "ED Sciences Chimiques", "154" => "ED Sciences de la Vie et de la Santé", "304" => "ED Sciences et environnements", "209" => "ED Sciences Physiques et de l'Ingénieur", "545" => "ED Sociétés, Politique, Santé Publique", "39" => "ED Mathématiques et Informatique");
        $students = "";
        while (!(is_array($students))) {
            $students = json_decode(file_get_contents(realpath(dirname(__FILE__)) . "/../../files/datas_adum/ubx_soutenances.json"), true);
        }
        $students = $students["data"][0];
        foreach ($students as &$value) {
            $value = array_extract($value, [ 
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
                "these_titre_anglais",
                "soutenanceJury",
                "these_heure_soutenance",
                "these_resume_anglais",
                "these_resume_fr",
                "these_soutenance_adresse",
                "these_soutenance_salle"
            ]);
        }

        $students = array_filter($students, function ($student) {
            return time() <= strtotime($student["these_date_soutenance"]);
        });

        usort($students, 'defense_sorter');

        $byGroup = group_by("these_date_soutenance", $students);
        foreach ($byGroup as &$valueByDate) {
            $valueByDate = group_by("these_ED_code", $valueByDate); 
        }

        foreach ($byGroup as $keyByDate => $valueByDate) {

            $datas_fr = array();
            $datas_en = array();
            foreach ($valueByDate as $keyByED => $valueByED) {
                $i = count($valueByED);
                if ($i > 1) {
                    $datas_fr["soutenances à " . $codes[$keyByED]] = $i;
                    $datas_en["PhD defenses from " . $codes[$keyByED]] = $i;
                } else {
                    $datas_fr["soutenance à " . $codes[$keyByED]] = $i;
                    $datas_en["PhD defense from " . $codes[$keyByED]] = $i;
                }
            }
            $desc_fr = get_show_key_numbers($datas_fr);
            $desc_en = get_show_key_numbers($datas_en);
            $details_fr = "";
            $details_en = "";
            foreach ($valueByDate as $keyByED => $valueByED) {
                $details_fr .= "<h4>" . $codes[$keyByED] . "</h4>";
                $details_en .= "<h4>" . $codes[$keyByED] . "</h4>";
                $details_fr .= "<ul>";
                $details_en .= "<ul>";
                foreach ($valueByED as $student) {
                    $details_fr .= get_display_defense_to_come($student, "FR");
                    $details_en .= get_display_defense_to_come($student, "EN");
                }
                $details_fr .= "</ul>";
                $details_en .= "</ul>";
            }
            $place = "Université de Bordeaux";
            addEvent($keyByDate, $desc_fr, $place, $details_fr, "FR");
            addEvent($keyByDate, $desc_en, $place, $details_en, "EN");
        }
    }

    update_events();

