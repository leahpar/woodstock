<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[Vich\Uploadable]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'medias')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    public ?Certificat $certificat = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(
        mapping: 'medias',
        fileNameProperty: 'fileName',
        size: 'fileSize',
        mimeType: 'fileMimeType',
        originalName: 'originalName')]
    public ?File $file = null;

    #[ORM\Column]
    public ?string $fileName = null;

    #[ORM\Column]
    public ?int $fileSize = null;

    #[ORM\Column]
    public ?string $fileMimeType = null;

    #[ORM\Column]
    public ?string $originalName = null;

    #[ORM\Column(nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;


    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if (null !== $file) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getType(): string
    {
        if (str_starts_with($this->fileMimeType, 'image')) {
            return 'image';
        }
        if ($this->fileMimeType == "application/pdf") {
            return 'PDF';
        }
    }
}
