<?php
namespace App\Controller;

use App\Entity\Student;
use App\Form\StudentType;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class HomeController extends AbstractController
{
    /**
     * @param StudentRepository $studentRepository
     * @return Response
     */
    #[Route('/', name: 'app_index')]
    public function index(StudentRepository $studentRepository): Response
    {
        $students = $studentRepository->findAll();
        return $this->render('index.html.twig', [
            'students' => $students
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    #[Route('/add', name: 'app_student_add')]
    #[IsGranted('ROLE_ADMIN')]
    public function addStudent(Request $request, EntityManagerInterface $em)
    {
        $student = new Student();
        $form = $this->createForm(StudentType::class, $student);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($student);
            $em->flush();

            $this->addFlash(
                'success',
                'Student was added successfully!'
            );

            return $this->redirectToRoute('app_index');
        }

        return $this->render('addStudent.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @param Request $request
     * @param ManagerRegistry $doctrine
     * @param int $id
     * @return Response
     */
    #[Route('/update/{id}', name: 'app_student_update')]
    public function updateStudent(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $student = $entityManager->getRepository(Student::class)->find($id);

        if (!$student) {
            throw $this->createNotFoundException(
                'No student found for id ' . $id
            );
        }

        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $firstName = $form->get('firstname')->getData();
            $lastname = $form->get('lastname')->getData();
            $student->setFirstname($firstName);
            $student->setLastname($lastname);
            $entityManager->flush();

            return $this->redirectToRoute('app_index');
        }
        return $this->render('updateStudent.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @param ManagerRegistry $doctrine
     * @param int $id
     * @param Request $request
     * @return Response
     */
    #[Route('/remove/{id}', name: 'app_student_remove')]
    public function removeStudent(ManagerRegistry $doctrine, int $id, Request $request): Response
    {
        $entityManager = $doctrine->getManager();
        $student = $entityManager->getRepository(Student::class)->find($id);

        if (!$student) {
            throw $this->createNotFoundException(
                'No student found for id ' . $id
            );
        }
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-student' . $student->getId(), $submittedToken)) {
            $entityManager->remove($student);
            $entityManager->flush();
        }


        return $this->redirectToRoute('app_index');
    }
}