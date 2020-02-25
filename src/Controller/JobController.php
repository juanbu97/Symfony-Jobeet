<?php
namespace App\Controller;

use App\Entity\Job;
use App\Entity\Category;
use App\Form\JobType;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class JobController extends AbstractController
{
    
    /**
     * Finds and displays a job entity
     * @param Job $job
     * @Entity("job", expr="repository.findActiveJobs(id)")
     * @Route("/job/{id}", name="job.show", methods="GET", requirements={"id" = "\d+"})
     * @return Response
     */
    public function show(Job $job) : Response
    {
        return $this->render('job/show.html.twig', [
            'job' => $job,
        ]);
    }
    
    /**
     * @Route("job/create", name="job.create", methods={"GET","POST"})
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

            return $this->redirectToRoute('job.preview' ,['token' => $job->getToken()]);
        }
        return $this->render('job/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * List all job entities
     * @Route("/", name="job.list")
     * @return Response
     */
    public function list(EntityManagerInterface $em) : Response
    {
        $categories = $em->getRepository(Category::class)->findWithActiveJobs();

        return $this->render('job/list.html.twig', [
            'categories' => $categories,
        ]);
    }

    /** Edit existing job entity
     * @Route("/job/{token}/edit", name="job.edit", methods={"GET", "POST"}, requirements={"token" = "\w+"})
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
            return $this->redirectToRoute('job.list');
        }
        return $this->render('job/edit.html.twig', [
            'form'=> $form->createView(),
            'job' => $job
            ]);
    }

    /**
     * Finds and displays the preview page for a job entity
     *
     * @Route("job/{token}", name="job.preview", methods="GET", requirements={"token" = "\w+"})
     * @param Job $job
     * @return Response
     *
     */
    public function preview(Job $job) : Response
    {
        $form = $this->createDeleteForm($job);
        $form2 = $this->createPublishForm($job);
        $form3 = $this->createExtendForm($job);
        return $this->render('job/show.html.twig', [
            'job' => $job,
            'hasControlAccess' => true,
            'deleteForm' => $form->createView(),
            'publishForm' => $form2->createView(),
            'extendForm' => $form3->createView()
            
        ]);
    }

    /**
     * Creates a form to delete a job entity
     *
     * @param Job $job
     * @return FormInterface
     */
    private function createDeleteForm(Job $job) : FormInterface
    {
        return $this->createFormBuilder()
        ->setAction($this->generateUrl('job.delete', ['token' => $job->getToken()]))
        ->setMethod('DELETE')
        ->getForm();
    }

    /**
     * Deletea job entity
     *
     * @Route("job/{token}/delete", name="job.delete" , methods="DELETE")
     * @param Request $request
     * @param Job $job
     * @param EntityManagerInterface $em
     */
    public function delete(Request $request, Job $job, EntityManagerInterface $em):Response
    {
        $form = $this->createDeleteForm($job);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->remove($job);
            $em->flush();
        }
        return $this->redirectToRoute('job.list');
    }

    /**
     * Creates a form to publish a job
     * @param Job $job
     *
     * @return FormInterface
     */
    private function createPublishForm(Job $job) : FormInterface
    {
        return $this->createFormBuilder()
        ->setAction($this->generateUrl('job.publish', ['token' => $job->getToken()]))
        ->setMethod('POST')
        ->getForm();
    }

    /**
     * Publish a job
     * 
     * @Route("job/{token}/publish", name="job.publish" , methods="POST" )
     * @param Request $request
     * @param Job $job
     * @param EntityManagerInterface $em
     * 
     * @return Response
     */
    public function publish(Request $request, Job $job, EntityManagerInterface $em) : Response
    {
        $form = $this->createPublishForm($job);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $job->setActivated(true);
            $em->flush();
            $this->addFlash('notice', 'Your job was published');
        }
        return $this->redirectToRoute('job.preview', ['token' => $job->getToken()]);
    }


    /**
     * Creates a form to extends job life
     * @param Job $job
     *
     * @return FormInterface
     */
    private function createExtendForm(Job $job) : FormInterface
    {
        return $this->createFormBuilder()
        ->setAction($this->generateUrl('job.extend', ['token' => $job->getToken()]))
        ->setMethod('POST')
        ->getForm();
    }
    /**
     * Extends job life
     * @Route("job/{token}/extend", name="job.extend", methods="POST")
     * 
     * @param Request $request
     * @param Job $job
     * @param EntityManagerInterface $em 
     */
    public function extends(Request $request, Job $job, EntityManagerInterface $em) : Response
    {
        $form = $this->createExtendForm($job);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $job->setExpiresAt(new \DateTime('30 days'));
            $em->flush();
            $this->addFlash('notice', 'Your job life was extended');
        }
        return $this->redirectToRoute('job.preview', ['token' => $job->getToken()]);
    }
   
}
