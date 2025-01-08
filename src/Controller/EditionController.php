<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use App\Entity\Edition;
use App\Form\EditionNewType;
use DateTime;

class EditionController extends BeehiveController{

  public function listAll(): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Edition::class);
    $editions = $repository->findAll(['sort' => 'DESC']);
    return $this->render('edition/listAll.html.twig', ['editions' => $editions]);
  }

  public function list(): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Edition::class);
    $editions = array();
    
    if ($this->request->getMethod() == 'POST') {
      
      // PARAMETERS
      $limit         = $this->getParameter('rows');
      $page          = $this->getParameter('page');
      $offset        = $page * $limit - $limit;
      $offset        = $offset < 0 ? 0 : $offset;
      $sort          = $this->getParameter('sidx');
      $sortDirection = $this->getParameter('sord');

      // ODER BY
      $orderBy = ' ORDER BY e.' . $sort . ' ' . $sortDirection;

      // WHERE
      $where = '';
      if($this->getParameter('_search') == 'true'){
        $where = '';
        $prefix = ' WHERE ';

        foreach(array('sort', 'title', 'collection', 'volume', 'remark', 'material') as $field){
          if(strlen($this->getParameter($field))){
            $where .= $prefix . 'e.' . $field . ' LIKE \'%' . $this->getParameter($field) . '%\'';
            $prefix = ' AND ';
          }
        }
      }

      // LIMIT
      $query = $entityManager->createQuery('SELECT count(e.id) FROM App\Entity\Edition e ' . $where);
      $count = $query->getSingleScalarResult();
      $totalPages = ($count > 0 && $limit > 0) ? ceil($count/$limit) : 0;

      // QUERY
      $query = $entityManager->createQuery('SELECT e FROM App\Entity\Edition e ' . $where . ' ' . $orderBy)->setFirstResult($offset)->setMaxResults($limit);
      
      $editions = $query->getResult();

      return $this->render('edition/list.xml.twig', ['editions' => $editions, 'count' => $count, 'totalPages' => $totalPages, 'page' => $page]);
    } else {
      return $this->render('edition/list.html.twig', ['editions' => $editions]);
    }
  }
  
  public function new(): Response {
    $edition = new Edition();
    
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Edition::class);

    $form = $this->createForm(EditionNewType::class, $edition);

    if ($this->request->getMethod() == 'POST') {
      $form->handleRequest($this->request);

      if ($form->isValid()) {
        $entityManager->persist($edition);
        $entityManager->flush();

        $this->addFlash('notice', 'Die Edition »' . $edition->getSort() . ' = ' . $edition->getTitle() . '« wurde angelegt.');

        return $this->redirect($this->generateUrl('PapyrillioBeehive_EditionList'));
      }
    }

    return $this->render('edition/new.html.twig', ['form' => $form->createView()]);
  }

  public function update(): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Edition::class);
    $edition = $repository->findOneBy(array('id' => $this->getParameter('id')));
    
    foreach(array('sort', 'title', 'remark', 'material') as $field){
      if($value = $this->getParameter($field)){
        $setter = 'set' . ucfirst($field);
        $getter = 'get' . ucfirst($field);

        $edition->$setter($value);
      }

    }

    $entityManager->flush();

    return new Response($edition);
  }

  public function delete($id): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Edition::class);
    $edition = $repository->findOneBy(array('id' => $id));

    $entityManager->remove($edition);
    $entityManager->flush();
    return $this->redirect($this->generateUrl('PapyrillioBeehive_EditionList'));
  }

  public function show($id): Response {

    if(!$id){
      return $this->forward('PapyrillioBeehiveBundle:Edition:list');
    }
    
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Edition::class);
    $edition = $repository->findOneBy(array('id' => $id));

    return $this->render('edition/show.html.twig', ['edition' => $edition]);
  }

}
