<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Form\BuyType;
use App\Form\ChargeType;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $form = $this->createForm(BuyType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $buy = $form->getData();

            /** @var User $user */
            $user = $buy['user'];

            /** @var Product $product */
            $product = $buy['product'];

            if ($user->getBalance() >= $product->getPrice()) {
                $user->setBalance($user->getBalance() - $product->getPrice());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('info', 'Item bought');
            } else {
                $this->addFlash('error', 'Not enought Money');
            }

            return $this->redirectToRoute('index');
        }

        return $this->render('index/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function register(Request $request)
    {
        $form = $this->createForm(UserType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('index');
        }


        return $this->render('register/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/charge", name="charge")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function charge(Request $request)
    {
        $form = $this->createForm(ChargeType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $charge = $form->getData();

            /** @var User $user */
            $user = $charge['user'];

            $amount = $charge['amount'] * 100;
            $user->setBalance($user->getBalance() + $amount);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('info', 'Youve charged up your Account by ' . $charge['amount'] . '€');

            return $this->redirectToRoute('index');
        }


        return $this->render('charge/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
