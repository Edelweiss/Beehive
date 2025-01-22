<?php

namespace App\Controller;

use Doctrine\ORM\Query\ResultSetMapping;
use App\Entity\Correction;
use App\Entity\Register;
use App\Controller\ApiaryController;
use Symfony\Component\HttpFoundation\Response;
use DOMDocument;
use DOMXPath;

class RegisterController extends BeehiveController{

  private static function compareDdb($ddb1, $ddb2){
    $a = explode(';', $ddb1['value']);
    $b = explode(';', $ddb2['value']);

    if(count($a) < 3 || count($b) < 3){
      return count($a) < count($b) ? -1 : 1;
    }

    if($a[0] != $b[0]){
      return $a[0] < $b[0] ? -1 : 1;
    }

    $a[1] = intval(preg_replace('/[^\d]+/', '', $a[1]));
    $a[2] = intval(preg_replace('/(.+;.*;[^ ]]+).*/', '$1', $a[2]));
    $b[1] = intval(preg_replace('/[^\d]+/', '', $b[1]));
    $b[2] = intval(preg_replace('/(.+;.*;[^ ]]+).*/', '$1', $b[2]));

    return ($a[1] * 1000000 + $a[2]) < ($b[1] * 1000000 + $b[2]) ? -1 : 1;
  }

  public function autocomplete($id = 0): Response {
    $term = $this->getParameter('term');
    $autocomplete = array();

    if(strlen($term)){
      $entityManager = $this->getDoctrine()->getManager();
      $repository = $entityManager->getRepository(Register::class);

      $search = 'r.ddb LIKE \'%' . $term . '%\' or r.dclp LIKE \'%' . $term . '%\'';
      $order = 'r.ddb, r.dclp';

      if(preg_match('/^\d/', $term)){
        $search = 'r.hgv LIKE \'' . $term . '%\' OR r.tm LIKE \'' . $term . '%\'';
        $order = 'r.tm, r.hgv';
      }

      $query = $entityManager->createQuery('SELECT r.id, r.ddb, r.dclp, r.tm, r.hgv FROM App\Entity\Register r WHERE ' . $search . ' ORDER BY ' . $order);
      $query->setMaxResults(1000);

      foreach($query->getResult() as $result){
        $caption = $this->makeCaption($result, $term);

        $autocomplete[] = array('id' => $result['id'], 'value' => $caption, 'label' => $caption);
      }
      if(preg_match('/^[^\d]/', $term)){
        usort($autocomplete, 'self::compareDdb');
      }
    }

    return new Response(json_encode(array_slice($autocomplete, 0, 20)));
  }

  public function autocompleteDdb(): Response {
    $term = $this->getParameter('term');

    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Register::class);

    $query = $entityManager->createQuery('SELECT DISTINCT r.ddb FROM App\Entity\Register r JOIN r.corrections c WHERE r.ddb LIKE \'' . $term . '%\' ORDER BY r.ddb');

    $autocomplete = array();
    foreach($query->getResult() as $result){
      $autocomplete[] = $result['ddb'];
    }
    return new Response(json_encode($autocomplete));
  }

  protected function makeCaption($result, $term){
    if(preg_match('/^\d/', $term)){
      $caption = ($result['tm'] ? $result['tm'] . ($result['hgv'] && ($result['hgv'] != $result['tm']) ? ' (' . str_replace($result['tm'], '', $result['hgv']) . ')' : '') : $result['hgv']);
      $caption .= ($result['ddb'] ? ' (' . $result['ddb'] . ')' : '');
      return $caption;
    }
    $caption = ($result['ddb'] && preg_match('/.*' . $term . '.*/', $result['ddb']) ? $result['ddb'] . ' ' : ($result['dclp'] && preg_match('/.*' . $term . '.*/', $result['dclp']) ? $result['dclp'] . ' ' : ''));
    $caption .= ($result['hgv'] || $result['tm'] ? ' TM/HGV ' : '');
    $caption .= ($result['tm'] ? $result['tm'] . ($result['hgv'] && ($result['hgv'] != $result['tm']) ? ' (' . str_replace($result['tm'], '', $result['hgv']) . ')' : '') : $result['hgv']);
    return $caption;
  }

  public function showAssignments($correctionId): Response {
    $correction = $this->getDoctrine()->getManager()->getRepository(Correction::class)->findOneBy(['id' => $correctionId]);

    return $this->render('register/snippetFolder.html.twig', ['correction' => $correction]);
  }

  public function assign($registerId, $correctionId): Response {
    $register = $this->getDoctrine()->getManager()->getRepository(Register::class)->findOneBy(array('id' => $registerId));
    $correction = $this->getDoctrine()->getManager()->getRepository(Correction::class)->findOneBy(array('id' => $correctionId));

    $correction->addRegisterEntry($register);
    $this->getDoctrine()->getManager()->persist($correction);
    $this->getDoctrine()->getManager()->flush();

    return $this->redirect($this->generateUrl('PapyrillioBeehive_RegisterShowAssignments', array('correctionId' => $correctionId)));
  }

  public function revokeAssignment($registerId, $correctionId): Response {
    $register = $this->getDoctrine()->getManager()->getRepository(Register::class)->findOneBy(array('id' => $registerId));
    $correction = $this->getDoctrine()->getManager()->getRepository(Correction::class)->findOneBy(array('id' => $correctionId));

    $register->getCorrections()->removeElement($correction);
    $correction->getRegisterEntries()->removeElement($register);

    $this->getDoctrine()->getManager()->persist($correction);
    $this->getDoctrine()->getManager()->flush();

    return $this->redirect($this->generateUrl('PapyrillioBeehive_RegisterShowAssignments', array('correctionId' => $correctionId)));
  }

  protected function getIdnoTriplet(){
    $newEntry = $this->getParameter('newEntry');

    $ddb = null;
    $tm  = null;
    $hgv = null;

    $ddbParse = preg_replace('/^([^; ]+\s+)?([\w\d\.]+;[^; ]*;[^; ]+)(\s+[^;]+)?$/', '$2', $newEntry);
    if(preg_match('/^[\w\d\.]+;[^; ]*;[^; ]+$/', $ddbParse)){
      $ddb = $ddbParse;
    }

    $tmParse = preg_replace('/^(.* )?(\d+)( .*)?$/', '$2', $newEntry);
    if(preg_match('/^\d+$/', $tmParse)){
      $tm = $tmParse;
    }

    $hgvParse = preg_replace('/^(.* )?(\d+[a-z]+)( .*)?$/', '$2', $newEntry); // nur was einen Buchstaben hat, kann klar als HGV-Nummer erkannt werden
    if(preg_match('/^\d+[a-z]+$/', $hgvParse)){
      $hgv = $hgvParse;
    }
    
    if(!$hgv && $tm && preg_match('/.*' . $tm . '.+' . $tm . '.*/', $newEntry)){ // sind HGV- und TM-Nummer gleich, müssen beide (= die gleiche Nummer zwei Mal) angegeben werden
      $hgv = $tm;
    }

    return array('tm' => $tm, 'hgv' => $hgv, 'ddb' => $ddb);
  }

  public function createAndAssign($correctionId): Response {
    $idnoTriplet = $this->getIdnoTriplet(); 

    $register = $this->getOrCreate($idnoTriplet['ddb'], $idnoTriplet['tm'], $idnoTriplet['hgv']);

    $correction = $this->getDoctrine()->getManager()->getRepository(Correction::class)->findOneBy(array('id' => $correctionId));

    $register->addCorrection($correction);
    $correction->addRegisterEntry($register);

    $this->getDoctrine()->getManager()->persist($correction);
    $this->getDoctrine()->getManager()->persist($register);
    $this->getDoctrine()->getManager()->flush();

    return $this->redirect($this->generateUrl('PapyrillioBeehive_RegisterShowAssignments', array('correctionId' => $correctionId)));
  }

  public function create(): Response {
    $idnoTriplet = $this->getIdnoTriplet(); 

    $register = $this->getOrCreate($idnoTriplet['ddb'], $idnoTriplet['tm'], $idnoTriplet['hgv']);

    return $this->render('register/snippetListEntry.html.twig', ['register' => $register]);

    //return $this->redirect($this->generateUrl('PapyrillioBeehive_RegisterShow', array('id' => $register->getId())));
  }

  public function show($id): Response {
    $register = $this->getDoctrine()->getManager()->getRepository(Register::class)->findOneBy(array('id' => $id));

    return $this->render('register/show.html.twig', ['register' => $register]);
  }
  
  private function getOrCreate($ddb = null, $tm = null, $hgv = null){
    $register = $this->getDoctrine()->getManager()->getRepository(Register::class)->findOneBy(array('ddb' => $ddb, 'tm' => $tm, 'hgv' => $hgv));

    if(!$register && ((isset($tm) && strlen($tm)) || (isset($hgv) && strlen($hgv)) || (isset($ddb) && strlen($ddb)))){
      $register = new Register();
      if($ddb && strlen($ddb)){
        $register->setDdb($ddb);
      }
      if($hgv && strlen($hgv)){
        $register->setHgv($hgv);
      }
      if($tm && strlen($tm)){
        $register->setTm($tm);
      }
      $this->getDoctrine()->getManager()->persist($register);
      $this->getDoctrine()->getManager()->flush();
    }

    return $register;
  }

   public function wizard($id): Response {
    $data = $this->getData($id);

    return new Response(json_encode(array('success' => true, 'data' => $data)));
  }

  public function apiary($id): Response {
    return $this->forward('App\Controller\ApiaryController::honey', array('type' => 'register', 'id' => $id, 'format' => 'plain'));
  }

  protected function getData($id = 0){
    $data = array('tm' => array(), 'hgv' => array(), 'ddb' => array(), 'bl' => array());

    if(!$id){
      return $data;
    }

    $register = $this->getDoctrine()->getManager()->getRepository(Register::class)->findOneBy(array('id' => $id));

    // TM, HGV, DDB
    $data['tm']  = $register->getTm();
    $data['hgv'] = $register->getHgv();
    $data['ddb'] = $register->getDdb();

    // BL EDITION & TEXT
    if(!$register->getCorrections()->isEmpty()){
      $correction = $register->getCorrections()->first();
      $data['bl'] = array('edition' => $correction->getEdition()->getId(), 'text' => $correction->getText());
    }
    return $data;
  }

  public function listAll($collection = 'ddb'){


    $sql = 'SELECT
      DISTINCT SUBSTRING_INDEX(ddb, ";", 2) AS series_volume,
      SUBSTRING_INDEX(ddb, ";", 1) AS series,
      SUBSTR(SUBSTRING_INDEX(ddb, ";", 2), INSTR(SUBSTRING_INDEX(ddb, ";", 2), ";") + 1) AS volume
      FROM `register` WHERE ddb IS NOT NULL ORDER BY `ddb`  ASC';

    $sql = "SELECT
  SUBSTRING_INDEX(ddb, ';', 2) AS series_volume,
  SUBSTRING_INDEX(ddb, ';', 1) AS series,
  SUBSTR(SUBSTRING_INDEX(ddb, ';', 2), INSTR(SUBSTRING_INDEX(ddb, ';', 2), ';') + 1) AS volume,
  count(*) AS count_corrections,
  v.title
FROM `register` AS r JOIN correction_register AS cr ON cr.register_id = r.id JOIN correction c ON c.id = cr.correction_id LEFT JOIN volume v ON r.volume_id = v.id
WHERE ddb IS NOT NULL
GROUP BY series_volume  
ORDER BY v.sort, `ddb`  ASC";

    $sql = "SELECT
            hybrid AS series_volume,
            SUBSTRING_INDEX(hybrid, ';', 1) AS series,
            SUBSTR(hybrid, INSTR(hybrid, ';') + 1) AS volume,
            count(*) AS count_corrections,
            title
            FROM `register` AS r JOIN correction_register AS cr ON cr.register_id = r.id JOIN correction c ON c.id = cr.correction_id " . ($collection == 'ddb' ? 'LEFT' : '' ) . " JOIN volume v ON r.volume_id = v.id
            WHERE " . $collection . " IS NOT NULL
            GROUP BY v.id
            ORDER BY v.sort  ASC";


    $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);

    return $this->render('register/listAll.html.twig', ['collection' => $collection, 'register' => $stmt->executeQuery()->fetchAllAssociative()]);

  }
}
