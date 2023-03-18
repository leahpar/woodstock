<?php

namespace App\Twig;

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


}
