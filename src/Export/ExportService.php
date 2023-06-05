<?php

namespace App\Export;

use \PhpOffice\PhpSpreadsheet as PS;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{

    private function formatDataAndAutosize(Worksheet $sheet, /*int $startRow,*/ array $colonnes, array $data)
    {
        $startRow = 1;

        // Insert data
        $sheet->fromArray(array_keys($colonnes), null, "A$startRow");
        $sheet->fromArray($data, null, "A" . ($startRow + 1));

        // Autosize columns
        $idx = 'A';
        foreach ($colonnes as $format) {
            $sheet->getStyle("$idx:$idx")->getNumberFormat()->setFormatCode($format);
            $sheet->getColumnDimension($idx)->setAutoSize(true);
            $idx++;
        }
    }

    public function exportComptable(array $data, string $filename)
    {

        $spreadsheet = new PS\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $colonnes = [
            'Journal'     => NumberFormat::FORMAT_TEXT,
            'Date'        => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'Cpte'        => NumberFormat::FORMAT_TEXT,
            'Analytique'  => NumberFormat::FORMAT_TEXT,
            'Libellé'     => NumberFormat::FORMAT_TEXT,
            'Débit'       => NumberFormat::FORMAT_NUMBER_00,
            'Crédit'      => NumberFormat::FORMAT_NUMBER_00,
        ];
        $this->formatDataAndAutosize($sheet, $colonnes, $data);

        $writer = PS\IOFactory::createWriter($spreadsheet, "Xlsx");

        // Stream du fichier dans une réponse symfony
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Cache-Control', 'private');

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->setCallback(function () use ($writer) {
            $writer->save("php://output");
        });
        return $response;

    }

}
