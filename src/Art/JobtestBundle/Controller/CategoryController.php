<?php

namespace Art\JobtestBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Art\JobtestBundle\Entity\Category;

/**
 * Category controller.
 *
 */
class CategoryController extends Controller
{
    /**
     * Finds and displays a Category entity.
     *
     */
    public function showAction($slug, $page)
    {
        $em = $this->getDoctrine()->getManager();
     
        $category = $em->getRepository('ArtJobtestBundle:Category')->findOneBySlug($slug);
     
        if (!$category) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $latestJob = $em->getRepository('ArtJobtestBundle:Job')->getLatestPost($category->getId());

        if($latestJob) {
            $lastUpdated = $latestJob->getCreatedAt()->format(DATE_ATOM);
        } else {
            $lastUpdated = new DateTime();
            $lastUpdated = $lastUpdated->format(DATE_ATOM);
        }
        
        /**
         * @todo : create paginator class or find ready for use solution (vendor package)
         */
        $total_jobs = $em->getRepository('ArtJobtestBundle:Job')->countActiveJobs($category->getId());
        $jobs_per_page = $this->container->getParameter('max_jobs_on_category');
        $last_page = ceil($total_jobs / $jobs_per_page);
        $previous_page = $page > 1 ? $page - 1 : 1;
        $next_page = $page < $last_page ? $page + 1 : $last_page;
        $category->setActiveJobs($em->getRepository('ArtJobtestBundle:Job')->getActiveJobs($category->getId(), $jobs_per_page, ($page - 1) * $jobs_per_page));

        $format = $this->get('request')->getRequestFormat();
     
        return $this->render('ArtJobtestBundle:Category:show.'.$format.'.twig', [
            'category' => $category,
            'last_page' => $last_page,
            'previous_page' => $previous_page,
            'current_page' => $page,
            'next_page' => $next_page,
            'total_jobs' => $total_jobs,
            'feedId' => sha1($this->get('router')->generate('art_jobtest_category', ['slug' => $category->getSlug(), 'format' => 'atom'], true)),
            'lastUpdated' => $lastUpdated,
        ]);
    }

}
