<?php

namespace App\Twig;

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
            new TwigFilter('euro',  [$this, 'euro'], ['is_safe' => ['html']]),
            new TwigFilter('pourcent',  [$this, 'pourcent'], ['is_safe' => ['html']]),
            new TwigFilter('qrcode',  [$this, 'qrcode'], ['is_safe' => ['html']]),
        ];
    }
    
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getReferer', $this->getReferer(...)),
            new TwigFunction('getSearchOrder', $this->getSearchOrder(...)),
        ];
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
        // TODO: remplacer par yaqrgen ?
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
