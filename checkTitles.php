public function verif_cotutelle_titles()
    {
        $students = json_decode(file_get_contents(realpath(dirname(__FILE__)) . $this->jsonFiles["inscrits"]), true);
        $old_students = json_decode(file_get_contents(realpath(dirname(__FILE__)) . $this->jsonFiles["inscrits.old"]), true);
        $students = $students["data"][0];
        $old_students = $old_students["data"][0];
        foreach ($students as &$value) {
            $value = $this->array_extract($value, [
                "Matricule_etudiant",
                "nom",
                "prenom",
                "these_cotutelle",
                "these_ED_code",
                "these_titre",
                "these_titre_anglais"
            ]);
        }
        foreach ($old_students as &$value) {
            $value = $this->array_extract($value, [
                "Matricule_etudiant",
                "nom",
                "prenom",
                "these_cotutelle",
                "these_ED_code",
                "these_titre",
                "these_titre_anglais"
            ]);
        }

        $byGroup = $this->group_by("these_cotutelle", $students);        
        $byGroupOld = $this->group_by("these_cotutelle", $old_students);
        
        $students=array();
        foreach($byGroup['OUI'] as $elt){
            $students[$elt['Matricule_etudiant']] = $elt;
        }
        $old_students=array();
        foreach($byGroupOld['OUI'] as $elt){
            $old_students[$elt['Matricule_etudiant']] = $elt;
        }
        $msg="";
        foreach( $students as $k => $v){
            if($v["these_titre"] != $old_students[$k]["these_titre"]){
                $msg.="Changement de titre détecté pour ".$v["nom"]." ".$v["prenom"]."\n";
            }
        }
        if(strlen($msg) > 0){                        
            $mailService = \Core::make('mail');
            $mailService->setSubject('Changement de titre cotutelle');
            $mailService->setBodyHTML($msg);
            $mailService->from('guillaume.blin@u-bordeaux.fr','Guillaume Blin' );
            $mailService->to('bf-cotutelle-doctorat@u-bordeaux.fr', 'Cotutelle');
            $mailService->sendMail();            
        }
    }
