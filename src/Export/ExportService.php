<?php

namespace App\Export;

use App\Entity\Reference;
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

    private function getWriterResponse(PS\Spreadsheet $spreadsheet, string $filename)
    {
        // Stream du fichier dans une réponse symfony
        $response = new StreamedResponse();
        $response->headers->set('Cache-Control', 'private');

        switch (pathinfo($filename, PATHINFO_EXTENSION)) {
            case 'xlsx':
                $writer = PS\IOFactory::createWriter($spreadsheet, "Xlsx");
                $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
                break;
            case 'csv':
                $writer = PS\IOFactory::createWriter($spreadsheet, "Csv");
                $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
                break;
            default:
                throw new \Exception("Format de fichier non supporté");
        }

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        $response->setCallback(function () use ($writer) {
            $writer->save("php://output");
        });
        return $response;
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
        return $this->getWriterResponse($spreadsheet, $filename);
    }

    public function exportInventaire(array $references, string $filename)
    {
        $spreadsheet = new PS\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $colonnes = [
            'Référence'       => NumberFormat::FORMAT_TEXT,
            'Nom'             => NumberFormat::FORMAT_TEXT,
            'Marque'          => NumberFormat::FORMAT_TEXT,
            'Catégorie'       => NumberFormat::FORMAT_TEXT,
            'Conditionnement' => NumberFormat::FORMAT_TEXT,
            'CodeCompta'      => NumberFormat::FORMAT_TEXT,
            'Seuil'           => NumberFormat::FORMAT_NUMBER,
            'Prix'            => NumberFormat::FORMAT_NUMBER_00,
            'Stock'           => NumberFormat::FORMAT_NUMBER,
            'PrixStock'       => NumberFormat::FORMAT_NUMBER_00,
        ];

        $data = array_map(
            fn (Reference $ref) => [
                $ref->reference,
                $ref->nom,
                $ref->marque,
                $ref->categorie,
                $ref->conditionnement,
                $ref->codeComptaCompte,
                $ref->seuil,
                $ref->prix,
                $ref->getQuantite(),
                $ref->getPrixStock(),
            ],
            $references
        );

        $this->formatDataAndAutosize($sheet, $colonnes, $data);

        // TOTAL
        $range = "J1:J".($sheet->getHighestRow());
        $cell = "J".($sheet->getHighestRow()+1);
        $sheet->setCellValue($cell, "=SUM($range)");

        return $this->getWriterResponse($spreadsheet, $filename);
    }

}
