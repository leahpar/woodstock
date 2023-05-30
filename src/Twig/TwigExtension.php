<?php

namespace App\Twig;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExtension extends AbstractExtension
{


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


}
