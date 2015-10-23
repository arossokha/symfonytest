<?php

namespace Art\JobtestBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Art\JobtestBundle\Entity\Affiliate;
use Art\JobtestBundle\Form\AffiliateType;

/**
 * Affiliate controller.
 *
 */
class AffiliateController extends Controller
{

    /**
     * Lists all Affiliate entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ArtJobtestBundle:Affiliate')->findAll();

        return $this->render('ArtJobtestBundle:Affiliate:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Affiliate entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Affiliate();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setIsActive(false);

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('affiliate_wait', array('id' => $entity->getId())));
        }

        return $this->render('ArtJobtestBundle:Affiliate:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Affiliate entity.
     *
     * @param Affiliate $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Affiliate $entity)
    {
        $form = $this->createForm(new AffiliateType(), $entity, array(
            'action' => $this->generateUrl('affiliate_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Affiliate entity.
     *
     */
    public function newAction()
    {
        $entity = new Affiliate();
        $form   = $this->createCreateForm($entity);

        return $this->render('ArtJobtestBundle:Affiliate:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Affiliate entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ArtJobtestBundle:Affiliate')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Affiliate entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArtJobtestBundle:Affiliate:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Affiliate entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ArtJobtestBundle:Affiliate')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Affiliate entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ArtJobtestBundle:Affiliate:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Affiliate entity.
    *
    * @param Affiliate $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Affiliate $entity)
    {
        $form = $this->createForm(new AffiliateType(), $entity, array(
            'action' => $this->generateUrl('affiliate_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Affiliate entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ArtJobtestBundle:Affiliate')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Affiliate entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('affiliate_edit', array('id' => $id)));
        }

        return $this->render('ArtJobtestBundle:Affiliate:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Affiliate entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ArtJobtestBundle:Affiliate')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Affiliate entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('affiliate'));
    }

    /**
     * Creates a form to delete a Affiliate entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('affiliate_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    public function waitAction()
    {
        return $this->render('ArtJobtestBundle:Affiliate:wait.html.twig');
    }
}
