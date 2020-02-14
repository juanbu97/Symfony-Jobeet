<?php
namespace App\Controller\Admin;

use App\Entity\Job;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Controller\Admin\CategoryController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

class CategoryController extends AbstractController
{
    /**
     * Lists all categories entities.
     * @Route("/admin/categories" , name="category.list", methods="GET")
     * @param EntityManagerInterface $em
     * @return Response
     *
     *
     */
    public function list(EntityManagerInterface $em) : Response
    {
        $categories = $em->getRepository(Category::class)->findAll();
        return $this->render('Admin/category/list.html.twig', [
        'categories' => $categories,
        ]);
    }

    /**
     * Create category
     *
     * @Route("admin/category/create" , name="category.create" , methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     *
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $em) : Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category); //Doctrine administra el objeto
            $em->flush(); //Se lanzan las sentencias sql de los objetos administrados por doctrine

            return $this->redirectToRoute('category.list', ['category'=> $category]);
        }
        return $this->render('Admin/category/create.html.twig', [
        'form' => $form->createView()
    ]);
    }

    /**
     * Edit category
     *
     * @Route("/admin/category/{id}/edit", name="category.edit", methods={"GET","POST"} , requirements={"id" = "\d+"})
     * @param Request $request
     * @param Category $category
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit(Request $request, Category $category, EntityManagerInterface $em) : Response
    {
        $form =  $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category); //Doctrine administra el objeto
            $em->flush();
            return $this->redirectToRoute('category.list');
        }
        return $this->render('Admin/category/edit.html.twig', [
            'form'=> $form->createView(),
            'category' => $category
            ]);
    }

    /**
     * Delete category.
     *
     * @Route("admin/category/{id}/delete", name="admin.category.delete", methods="DELETE",
     * requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param Category $category
     *
     * @return Response
     */
    public function delete(Request $request, EntityManagerInterface $em, Category $category) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $em->remove($category);
            $em->flush();
        }
        return $this->redirectToRoute('category.list');
    }
}
