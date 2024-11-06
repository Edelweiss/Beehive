<?php

namespace App\Controller;

use App\Entity\Compilation;
use App\Entity\Correction;
use App\Entity\IndexEntry;
use Symfony\Component\HttpFoundation\Response;

class TinkerkitController extends BeehiveController{

  public function indexEntryAssign(): Response {
    if ($this->request->getMethod() == 'POST') {
      // PARAMETERS
      $indexEntryId = $this->getParameter('indexEntryId');
      $correctionId = $this->getParameter('correctionId');
      
      $compilationId = $this->getParameter('compilationId');
      $type          = $this->getParameter('type');
      $topic         = $this->getParameter('topic');
      $search        = $this->getParameter('search');
      $page          = $this->getParameter('page');

      $entityManager = $this->getDoctrine()->getManager();
      $repositoryI = $entityManager->getRepository(IndexEntry::class);
      $repositoryC = $entityManager->getRepository(Correction::class);
      $indexEntry = $repositoryI->findOneBy(['id' => $indexEntryId]);
      $correction = $repositoryC->findOneBy(['id' => $correctionId]);
      if($indexEntry && $correction){
        $indexEntry->addCorrection($correction);
        //$correction->addIndexEntry();
        $entityManager->persist($indexEntry);
        $entityManager->flush();
      }
      return $this->redirect($this->generateUrl('PapyrillioBeehive_IndexEntryManageAssignments', ['compilationId' => $compilationId, 'type' => $type, 'topic' => $topic, 'search' => ($page && !$search ? '_' : $search), 'page' => $page]));
    }
    return $this->redirect($this->generateUrl('PapyrillioBeehive_IndexEntryManageAssignments'));
  }

  public function indexEntryRelease(): Response {
    if ($this->request->getMethod() == 'POST') {
      // PARAMETERS
      $indexEntryId = $this->getParameter('releaseIndexEntryId');
      $correctionId = $this->getParameter('releaseCorrectionId');
      
      $compilationId = $this->getParameter('compilationId');
      $type          = $this->getParameter('type');
      $topic         = $this->getParameter('topic');
      $search        = $this->getParameter('search');
      $page          = $this->getParameter('page');

      $entityManager = $this->getDoctrine()->getManager();
      $repositoryI = $entityManager->getRepository(IndexEntry::class);
      $repositoryC = $entityManager->getRepository(Correction::class);
      $indexEntry = $repositoryI->findOneBy(['id' => $indexEntryId]);
      $correction = $repositoryC->findOneBy(['id' => $correctionId]);
      if($indexEntry && $correction){
        $indexEntry->removeCorrection($correction);
        //$correction->addIndexEntry();
        $entityManager->persist($indexEntry);
        $entityManager->flush();
      }
      return $this->redirect($this->generateUrl('PapyrillioBeehive_IndexEntryManageAssignments', ['compilationId' => $compilationId, 'type' => $type, 'topic' => $topic, 'search' => ($page && !$search ? '_' : $search), 'page' => $page]));
    }
    return $this->redirect($this->generateUrl('PapyrillioBeehive_IndexEntryManageAssignments'));
  }

  public function manageIndexEntryAssignments($compilationId, $type, $topic, $search = null, $page = null): Response {
    if($search == '_'){
      $search = null;
    }
    return $this->render('tinkerkit/manageIndexEntryAssignments.html.twig', [
      'compilationId'   => $compilationId, 'type' => $type, 'topic' => $topic, 'search' => $search, 'page' => $page,
      'compilationList' => $this->getCompilationList(),
      'topicList'       => $this->getTopicList(),
      'indexEntryList'  => $this->getIndexEntryList($compilationId, $type, $topic, $page),
      'correctionList'  => $this->getCorrectionList($compilationId, $search, $page)]);
  }
  private function getCompilationList(){
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Compilation::class);
    return $repository->findBy(['collection' => 'BL'], ['volume' => 'ASC', 'id' => 'ASC']);
  }
  private function getTopicList(){
    $res = $this->getDoctrine()->getManager()->createQueryBuilder()
      ->select('ie.topic')->distinct()
      ->from('App\Entity\IndexEntry', 'ie')
      ->orderBy('ie.sort', 'ASC')
      ->getQuery()->getResult();

    $topicList = [];
    foreach($res as $topicItem){
      $topicList[] = $topicItem['topic'];
    }
    return $topicList;
  }
  private function getIndexEntryList($compilationId, $type, $topic, $page = null){
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(IndexEntry::class);
    $parameters = ['type'=> $type, 'topic' => $topic, 'compilationId' => $compilationId];
    if($page){
      //$parameters['page'] = '%' . $page . '%';
    }

    $queryB = $entityManager->createQueryBuilder()
     ->select(['i, c'])
     ->from('App\Entity\IndexEntry', 'i')
     ->join('i.compilations', 'comp')
     ->leftjoin('i.corrections', 'c')
     ->where('comp.id = :compilationId AND i.topic = :topic AND i.type = :type AND (c IS NULL OR c.compilation = :compilationId)') //  . ($page ? ' AND (c IS NULL OR c.compilationPage LIKE :page)' : '')
     ->orderBy('i.sort', 'ASC')
     ->setParameters($parameters);
    return $queryB->getQuery()->getResult();
  }
  private function getCorrectionList($compilationId, $search, $page){
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Correction::class);
    $parameters = ['compilationId' => $compilationId];
    if($search){
      $parameters['search'] = '%' . $search . '%';
    }
    if($page){
      $parameters['page'] = '%' . $page . '%';
    }

    $queryB = $entityManager->createQueryBuilder()
     ->select(['c', 'e', 'i'])
     ->from('App\Entity\Correction', 'c')
     ->join('c.edition', 'e')
     ->leftjoin('c.indexEntries', 'i')
     ->where('c.compilation = :compilationId' . ($search ? ' AND c.description LIKE :search' : '') . ($page ? ' AND c.compilationPage LIKE :page' : ''))
     ->orderBy('c.sort', 'ASC')
     ->setParameters($parameters);
    return $queryB->getQuery()->getResult();
  }

/*
  public function assignIndexEntry($correctionId, $indexEntryIdList): Response {
    $correction = $this->getDoctrine()->getManager()->getRepository(Correction::class)->findOneBy(array('id' => $correctionId));
    foreach($indexEntryIdList as $indexEntryId){
      $indexEntry = $this->getDoctrine()->getManager()->getRepository(IndexEntry::class)->findOneBy(array('id' => $indexEntryId));
      $correction->addIndexEntry($indexEntry);
    }
    $this->getDoctrine()->getManager()->persist($correction);
    $this->getDoctrine()->getManager()->flush();

    return $this->redirect($this->generateUrl('PapyrillioBeehive_IndexEntryManageAssignments', array('correctionId' => $correctionId))); // CL todo
  }

  public function revokeAssignment($correctionId, $indexEntryId): Response {
    $correction = $this->getDoctrine()->getManager()->getRepository(Correction::class)->findOneBy(array('id' => $correctionId));
    $indexEntry = $this->getDoctrine()->getManager()->getRepository(IndexEntry::class)->findOneBy(array('id' => $indexEntryId));

    $correction->getRegisterEntries()->removeElement($register);
    $indexEntry->getCorrections()->removeElement($correction);

    $this->getDoctrine()->getManager()->persist($correction);
    $this->getDoctrine()->getManager()->flush();

    return $this->redirect($this->generateUrl('PapyrillioBeehive_IndexEntryManageAssignments', array('correctionId' => $correctionId))); // CL todo
  }
    */
}
