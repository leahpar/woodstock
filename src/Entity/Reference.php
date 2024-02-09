<?php

namespace App\Entity;

use App\Logger\LoggableEntity;
use App\Repository\ReferenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReferenceRepository::class)]
#[UniqueEntity('reference')]
class Reference extends LoggableEntity
{

    public const CATEGORIES = [
        // "BOIS"
        "CHARPENTE" => 60120000,
        "OSSATURE" => 60120000,
        "CHARPENTE DOUGLAS" => 60120000,
        "LAMELLES"=> 60120000,
        // "QUINCAILLERIE"
        "EQUERRES" => 60130000,
        "FIXATIONS" => 60130000,
        "BOULONNERIE CHARPENTE" => 60130000,
        "ROULEAUX" => 60130000,
        "SABOTS" => 60130000,
        "ETRIERS" => 60130000,
        "CARTOUCHE" => 60130000,
        // "AUTRES"
        "ECHAFAUDAGE" => 60150000,
        "PANNEAUX" => 60120000,
        "CONSOMMABLE & PETIT EQUIPEMENT" => 60630000,
        "EPI" => 60631000,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    public string $reference;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $codeComptaCompte = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    public string $nom;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $marque = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $categorie = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $conditionnement = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $commentaire = null;

    #[ORM\Column(nullable: true)]
    public ?float $prix = null;

    #[ORM\OneToMany(mappedBy: 'reference', targetEntity: Stock::class, fetch: 'EAGER', orphanRemoval: true)]
    #[Ignore]
    public Collection $stocks;

    #[ORM\Column(nullable: true)]
    public ?int $seuil = null;

    // Gestion par volume

    #[ORM\Column(nullable: true)]
    public ?float $largeur = null; // (mm)

    #[ORM\Column(nullable: true)]
    public ?float $hauteur = null; // (mm)

    #[ORM\Column(nullable: true)]
    public ?float $longueur = null; // (mm)

    #[ORM\Column(nullable: true)]
    public ?float $prixm3 = null; // (€/m3)

    #[ORM\Column(nullable: true)]
    public ?string $essence = null;


    public function __construct()
    {
        $this->stocks = new ArrayCollection();
    }

    public function addStock(Stock $stock): self
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->reference = $this;
        }

        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        if ($this->stocks->removeElement($stock)) {
            $stock->reference = null;
        }
        return $this;
    }

    public function getQuantite(): int
    {
        $quantite = 0;
        /** @var Stock $stock */
        foreach ($this->stocks as $stock) {
            if ($stock->isEntree()) {
                $quantite += $stock->quantite;
            } else {
                $quantite -= $stock->quantite;
            }
        }
        return $quantite;
    }
    // Fonction bidon, juste pour que le formulaire fonctionne
    public function setQuantite(int $qte): void {}

    public function getPrixStock(): float
    {
        $prix = 0.0;
        /** @var Stock $stock */
        foreach ($this->stocks as $stock) {
            if ($stock->isEntree()) {
                $prix += $stock->quantite * $stock->prix;
            } else {
                $prix -= $stock->quantite * $stock->prix;
            }
        }
        return $prix;
    }

    public function __toString(): string
    {
        return "[$this->reference] $this->nom ($this->conditionnement)";
    }


    public static function categoriesChoices(): array
    {
        return array_combine(
            array_keys(self::CATEGORIES),
            array_keys(self::CATEGORIES)
        );
    }
    public static function categoriesCodeDataMapping(): array
    {
        // Return [categorie => ["data-code" => codeCompta]]
        return array_combine(
            array_keys(self::CATEGORIES),
            array_map(
                fn($codeCompta) => ["data-code" => $codeCompta],
                array_values(self::CATEGORIES)
            ),
        );
    }
    public static function codeComptaChoices(): array
    {
        return array_combine(
            array_map(
                fn($codeCompta) => $codeCompta . " (" . implode(', ', array_keys(self::CATEGORIES, $codeCompta)) . ")",
                array_unique(array_values(self::CATEGORIES))
            ),
            array_map(
                fn($codeCompta) => $codeCompta,
                array_unique(array_values(self::CATEGORIES))
            ),
        );
    }

    public function calcPrix(): float
    {
        return round(($this->largeur??0)
             * ($this->hauteur??0)
             * ($this->longueur??0)
             * ($this->prixm3??0) / 1_000_000.0 ,2);
    }

}
