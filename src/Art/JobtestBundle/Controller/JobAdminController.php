<?php

namespace Art\JobtestBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class JobAdminController extends Controller
{
    public function batchActionExtend(ProxyQueryInterface $selectedModelQuery)
    {
        if ($this->admin->isGranted('EDIT') === false || $this->admin->isGranted('DELETE') === false) {
            throw new AccessDeniedException();
        }
 
        $modelManager = $this->admin->getModelManager();
 
        $selectedModels = $selectedModelQuery->execute();
 
        try {
            foreach ($selectedModels as $selectedModel) {
                $selectedModel->extend();
                $modelManager->update($selectedModel);
            }
        } catch (Exception $e) {
            $this->get('session')->getFlashBag()->add('sonata_flash_error', $e->getMessage());
 
            return new RedirectResponse($this->admin->generateUrl('list',$this->admin->getFilterParameters()));
        }
 
        $this->get('session')->getFlashBag()->add('sonata_flash_success',  sprintf('The selected jobs validity has been extended until %s.', date('m/d/Y', time() + 86400 * 30)));
 
        return new RedirectResponse($this->admin->generateUrl('list',$this->admin->getFilterParameters()));
    }

    public function batchActionDeleteNeverActivatedIsRelevant()
    {
        return true;
    }

    public function batchActionDeleteNeverActivated()
    {
        if ($this->admin->isGranted('EDIT') === false || $this->admin->isGranted('DELETE') === false) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $nb = $em->getRepository('ArtJobtestBundle:Job')->cleanup(60);

        if ($nb) {
            $this->get('session')->getFlashBag()->add('sonata_flash_success',  sprintf('%d never activated jobs have been deleted successfully.', $nb));
        } else {
            $this->get('session')->getFlashBag()->add('sonata_flash_info',  'No job to delete.');
        }

        return new RedirectResponse($this->admin->generateUrl('list',$this->admin->getFilterParameters()));
    }
}