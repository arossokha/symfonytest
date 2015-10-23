<?php

namespace Art\JobtestBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Art\JobtestBundle\Entity\Job;
use Art\JobtestBundle\Entity\Affiliate;
//use Art\JobtestBundle\Repository\AffiliateRepository;

/**
 * Apicontroller.
 *
 */
class ApiController extends Controller
{
    public function listAction(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();

        $jobs = [];

        $rep = $em->getRepository('ArtJobtestBundle:Affiliate');
        $affiliate = $rep->getForToken($token);

        if(!$affiliate) {
            throw $this->createNotFoundException('This affiliate account does not exist!');
        }

        $rep = $em->getRepository('ArtJobtestBundle:Job');
        $active_jobs = $rep->getActiveJobs(null, null, null, $affiliate->getId());

        foreach ($active_jobs as $job) {
            $jobs[$this->get('router')->generate('art_job_show', [
                'company' => $job->getCompanySlug(),
                'location' => $job->getLocationSlug(),
                'id' => $job->getId(),
                'position' => $job->getPositionSlug()
                ], true)
            ] = $job->asArray($request->getHost());
        }

        $format = $request->getRequestFormat();
        $jsonData = json_encode($jobs);

        if ($format == "json") {
            $headers = ['Content-Type' => 'application/json'];
            $response = new Response($jsonData, 200, $headers);

            return $response;
        }

        return $this->render('ArtJobtestBundle:Api:jobs.' . $format . '.twig', ['jobs' => $jobs]);
    }
}
