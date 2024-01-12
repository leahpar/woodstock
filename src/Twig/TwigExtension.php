<?php

namespace App\Twig;

use App\Entity\Reference;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{

    private ?Request $request = null;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('number',  [$this, 'number']),
            new TwigFilter('selectLabel',  [$this, 'selectLabel']),
            new TwigFilter('euro',  [$this, 'euro'], ['is_safe' => ['html']]),
            new TwigFilter('pourcent',  [$this, 'pourcent'], ['is_safe' => ['html']]),
            new TwigFilter('qrcode',  [$this, 'qrcode'], ['is_safe' => ['html']]),
        ];
    }
    
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getRoles', [$this, 'getRoles']),
            new TwigFunction('getCategories', [$this, 'getCategories']),
            new TwigFunction('getSearchOrder', $this->getSearchOrder(...)),
            new TwigFunction('getReferer', $this->getReferer(...)),
        ];
    }

    public function selectLabel($choices, ?string $value = null): ?string
    {
        $res = null;
        foreach ($choices as $k => $v) {
            if (is_iterable($v)) {
                $res = $this->selectLabel($v, $value);
            }
            elseif ($v->value == $value) {
                $res = $v->label;
            }
        }
        return $res;
    }

    public function number(?string $value = null, ?string $format = null, ?string $symbol = null): ?string
    {
        // https://www.php.net/manual/fr/class.numberformatter.php

        $str = $value;
        try {
            $f = new \NumberFormatter("fr", \NumberFormatter::DECIMAL);
            $f->setAttribute(\NumberFormatter::FRACTION_DIGITS, $format);

            return $f->format($f->parse(str_replace('.', ',', $value)))
                . '&nbsp;' . $symbol;
        }
        catch (\Exception $e) {
            // Do nothing
        }
        return $str;
    }

    public function euro(?string $value = null): ?string
    {
        return $this->number($value, 2, "€");
    }

    public function pourcent(?string $value = null): ?string
    {
        return $this->number($value * 100, 2, "%");
    }

    public function qrCode(?string $value = null): ?string
    {
        $options = [
            'version' => 3, // https://www.qrcode.com/en/about/version.html
            //'versionMin' => 5,
            //'versionMax' => 10,
            'eccLevel' => QRCode::ECC_M,
            //'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'imageTransparent' => true,
        ];

        $qrcode = new QRCode(new QROptions($options));
        return $qrcode->render($value);
    }

    public function getCategories(): array
    {
        return array_keys(Reference::CATEGORIES);
    }

    /**
     * NB: la liste doit correspondre à Ap\Entity\Role::cases()
     */
    public function getRoles(): array
    {
        return [
            'Admin' => [
                'ROLE_ADMIN' => 'Administrateur (accès à tout)',
            ],
            'Compta' => [
                'ROLE_COMPTA' => 'Exports comptables',
            ],
            'Utilisateurs' => [
                'ROLE_USER_LIST' => 'Consulter les utilisateurs',
                'ROLE_USER_EDIT' => 'Modifier les utilisateurs',
            ],
            'Chantiers' => [
                'ROLE_CHANTIER_LIST' => 'Consulter les chantiers',
                'ROLE_CHANTIER_EDIT' => 'Modifier les chantiers',
            ],
            'Matériel' => [
                'ROLE_MATERIEL_LIST' => 'Consulter le matériel',
                'ROLE_MATERIEL_EDIT' => 'Modifier le matériel',
            ],
            'Certificat' => [
                'ROLE_CERTIFICAT_LIST' => 'Consulter les certificats',
                'ROLE_CERTIFICAT_EDIT' => 'Modifier les certificats',
            ],
            'Stock' => [
                'ROLE_REFERENCE_LIST' => 'Consulter les références',
                'ROLE_REFERENCE_EDIT' => 'Modifier les références',
                'ROLE_REFERENCE_STOCK' => 'Modifier les stocks',
            ],
            'Planning' => [
//                'ROLE_PLANNING_LIST' => 'Consulter le planning',
                'ROLE_PLANNING_EDIT' => 'Modifier le planning',
            ],
        ];
    }

    public function getSearchOrder($search, $field): ?string
    {
        // $search = route.params
        if (($search['tri']??null) == $field) {
            return $search['order']??null;
        }
        return null;
    }

    public function getReferer(): string
    {
        return $this->request->headers->get('referer', '/');
    }

}
