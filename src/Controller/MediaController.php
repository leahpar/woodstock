<?php

namespace App\Controller;

use App\Entity\Certificat;
use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MediaController extends CommonController
{
    #[Route('/medias/upload', name: 'media_upload', methods: ['POST'])]
    public function upload(
        Request $request,
        EntityManagerInterface $em
    ): Response
    {
        $certificatId = $request->request->getInt('certificat');
        $certificat = $em->getRepository(Certificat::class)->find($certificatId);


        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');

        // Check type
        $allowedTypes = ['image/', 'application/pdf'];
        $type = $uploadedFile->getMimeType();
        $allowed = array_reduce(
            $allowedTypes,
            fn($acc, $allowedType) => $acc || str_starts_with($type, $allowedType),
            false);
        if (!$allowed) {
            return new Response("Type de fichier non autorisé ($type)", 400);
        }

        $media = new Media();

        $media->setFile($uploadedFile); // Here goes the magic (VichUploaderBundle)
        $media->certificat = $certificat;

        $em->persist($media);
        $this->log('doc_upload', $certificat, ['document' => $media->originalName]);
        $em->flush();


        return new Response(null, 201);
    }

    #[Route('/medias/{id}', name: 'media_delete', methods: ['DELETE'])]
    public function delete(Request $request, Media $media, EntityManagerInterface $em): Response
    {
        $em->remove($media);
        $this->log('doc_delete', $media->certificat, ['document' => $media->originalName]);
        $em->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }
}
