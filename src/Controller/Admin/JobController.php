<?php
namespace App\Controller\Admin;

use App\Entity\Job;
use App\Entity\Category;
use App\Form\JobType;
use App\Controller\Admin\CategoryController;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

class JobController extends AbstractController
{
    /**
     * Lists all job entities.
     * @Route("/admin/jobs/{page}" , name="admin.job.list", methods="GET", defaults={"page":1}, requirements={"page" ="\d+"})
     * @param EntityManagerInterface $em
     * @param PaginatorIterface $paginator
     * @param Int $page
     * @return Response
     *
     *
     */
    public function list(EntityManagerInterface $em, int $page,PaginatorInterface $paginator) : Response
    {
        $jobs = $paginator->paginate(
            $em->getRepository(Job::class)->findAll(),
            $page,
            $this->getParameter('max_per_pag'));
        
        return $this->render('Admin/job/list.html.twig', [
        'jobs' => $jobs,
        ]);
    }

    /**
     * @Route("admin/job/create", name="admin.job.create", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response/RedirectResponse
     */
    public function create(Request $request, EntityManagerInterface $em, FileUploader $fileUploader) : Response
    {
        $job = new Job();
        $form = $this->createForm(JobType::class, $job);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var UploadedFile|null $logoFile
             */
            $logoFile = $form->get('logo')->getData();

            if ($logoFile instanceof UploadedFile) {
                $fileName = $fileUploader->upload($logoFile);
                $job->setLogo($fileName);
            }
            $em->persist($job); //Doctrine administra el objeto
            $em->flush(); //Se lanzan las sentencias sql de los objetos administrados por doctrine

            return $this->redirectToRoute('admin.job.list');
        }
        return $this->render('Admin/job/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /** Edit existing job entity
     * @Route("admin/job/{id}/edit", name="admin.job.edit", methods={"GET", "POST"}, requirements={"id" = "\d+"})
     * @param Request $request
     * @param Job $job
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit(Request $request, Job $job, EntityManagerInterface $em) : Response
    {
        $form =  $this->createForm(JobType::class, $job);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var UploadedFile|null $logoFile
             */
            $logoFile = $form->get('logo')->getData();

            if ($logoFile instanceof UploadedFile) {
                $fileName = $fileUploader->upload($logoFile);
                $job->setLogo($fileName);
            }
            $em->persist($job); //Doctrine administra el objeto
            $em->flush();
            return $this->redirectToRoute('admin.job.list');
        }
        return $this->render('Admin/job/edit.html.twig', [
            'form'=> $form->createView(),
            'job' => $job
            ]);
    }

    /**
     * Delete a job.
     *
     * @Route("admin/job/{id}/delete", name="admin.job.delete", methods="DELETE",
     * requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param Job $job
     *
     * @return Response
     */
    public function delete(Request $request, EntityManagerInterface $em, Job $job) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $job->getId(), $request->request->get('_token'))) {
            $em->remove($job);
            $em->flush();
        }
        return $this->redirectToRoute('admin.job.list');
    }
}
