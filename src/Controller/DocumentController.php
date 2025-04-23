<?php

namespace App\Controller;

use App\Entity\Certificat;
use App\Entity\Document;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DocumentController extends CommonController
{
    #[Route('/documents/upload', name: 'document_upload', methods: ['POST'])]
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

        $document = new Document();

        $document->setFile($uploadedFile); // Here goes the magic (VichUploaderBundle)
        $document->certificat = $certificat;

        $em->persist($document);
        $this->log('doc_upload', $certificat, ['document' => $document->originalName]);
        $em->flush();


        return new Response(null, 201);
    }

    #[Route('/documents/{id:document}', name: 'document_delete', methods: ['DELETE'])]
    public function delete(Request $request, Document $document, EntityManagerInterface $em): Response
    {
        $em->remove($document);
        $this->log('doc_delete', $document->certificat, ['document' => $document->originalName]);
        $em->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }
}
