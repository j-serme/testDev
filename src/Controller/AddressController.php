<?php

namespace App\Controller;

use App\Entity\Adress;
use App\Entity\User;
use App\Form\AddressType;
use App\Form\EditAddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/address")
 */
class AddressController extends AbstractController
{
    /**
     * @Route("/{id}", name="address")
     */
    public function new(Request $request, EntityManagerInterface $manager): Response
    {

        $address = new Adress();
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $address->setUser($this->getUser());
            $manager->persist($address);
            $manager->flush();

            $this->addFlash('notice', 'L\'adresse a bien été ajoutée');
            return $this->redirectToRoute('profile', ['id'=> $this->getUser()->getId()]);

        }


        return $this->renderForm('address/index.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/change/{id}", name="address_change")
     * @param Adress $address
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function change(Adress $address, Request $request, EntityManagerInterface $manager)
    {
        $formAddress = $this->createForm(EditAddressType::class, $address);
        $formAddress->handleRequest($request);

        if ($formAddress->isSubmitted() && $formAddress->isValid())
        {
            $address = $formAddress->getData();
            $address->setUser($this->getUser());
            $manager->persist($address);
            $manager->flush();

            $this->addFlash('notice', 'L\'adresse a bien été mise à jour' );
            return $this->redirectToRoute('profile');
        }

        return $this->renderForm('address/edit.html.twig', ['formAddress' => $formAddress]);
    }

    /**
     * @Route("/delete/{id}",name="address_delete")
     * @param Adress $address
     * @param EntityManagerInterface $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Adress $address, EntityManagerInterface $manager)
    {
        if (!$address)
        {
            $this->addFlash('notice', 'Veuillez supprimer une adresse déjà existante' );

        }

        $manager->remove($address);
        $manager->flush();

        $this->addFlash('notice', 'L\'adresse a bien été supprimée');
        return $this->redirectToRoute('profile');
    }
}
