<?php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Event;
use App\Entity\Comment;

class EventController extends AbstractController
{
    /**
     * @Route("/events/new", name="event_new")
     */
    public function new(Request $request)
    {
        $event = new Event("New Event");

        $form = $this->createFormBuilder($event)
            ->add('title', TextType::class)
            ->add('description', TextareaType::class, [
                'help' => 'Event description (Markdown enabled)', 
                'attr' => ['rows' => 12]
            ])
            ->add('organizer', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Event'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();
    
            return $this->redirectToRoute('event_show', array(
                'id' => $event->getId()
            ));
        }

        return $this->render('event/new.html.twig', [
            'form' => $form->createView(),
        ]);    
            
    }

	/**
     * @Route("/events/{id}", name="event_show")
     */
    public function show(Request $request, Event $event)
    {
        $comment = new Comment();
        $comment->setEvent($event);
        $commentForm = $this->createFormBuilder($comment)
            ->add('author', TextType::class)
            ->add('message', TextareaType::class, [
                'attr' => ['rows' => 4]
            ])
            ->add('save', SubmitType::class, ['label' => 'Post comment'])
            ->getForm();

        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment = $commentForm->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
        }

        return $this->render('event/show.html.twig', [
            'event' => $event,
            'commentForm' => $commentForm->createView()
        ]);
    }
}
