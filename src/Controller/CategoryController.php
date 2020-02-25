<?php

namespace App\Controller;

use App\Entity\Job;
use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Knp\Component\Pager\PaginatorInterface;

class CategoryController extends AbstractController
{


    /**
     * 
     * @Route("/category/{slug}/{page}", name="category.show", methods="GET", defaults={"page":1}, 
     * requirements={"page" ="\d+"})
     * @param Category $category
     * @param PaginatorIterface $paginator
     * @return Response
     */
    public function show(Category $category, PaginatorInterface $paginator, int $page) : Response
    {
        

        $activeJobs = $paginator->paginate(
            $this->getDoctrine()->getRepository(Job::class)->getPaginatedActiveJobsByCategoryQuery($category),
            $page,
            $this->getParameter('max_jobs_on_category'));

        // parameters to template
        return $this->render('category/show.html.twig', ['category' => $category, 'activeJobs' => $activeJobs] );
    }
}
