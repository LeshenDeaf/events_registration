<?php

namespace Vyatsu\Events\Utils;

require $_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php";

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Excel
{
	private Spreadsheet $spreadsheet;

	public function __construct()
	{
		$this->spreadsheet = new Spreadsheet();
	}

	public function makeExcel(
		array $formFields,
		array $results,
		string $fileName
	): void {
		$sheet = $this->spreadsheet->getActiveSheet();
		$j = 1;
		$i = 1;

		$this->writeHeader($i, $j, $formFields);

		foreach ($results as $formRes) {
			++$i;

			$j = 0;

			$sheet->setCellValueByColumnAndRow(++$j, $i, $i - 1);

			foreach ($formRes->getResult() as $field) {
				$this->writeCell($i, $j, $field);
			}
		}

		static::saveSpreadSheet($this->spreadsheet, $fileName);
	}


	public static function saveSpreadSheet(
		Spreadsheet $spreadsheet,
		string $fileName
	): void {
		$writer = new Xlsx($spreadsheet);
		$writer->save($fileName);
	}

	private function writeHeader(int &$i, int &$j, array $formFields): void
	{
		$sheet = $this->spreadsheet->getActiveSheet();
		$sheet->setCellValueByColumnAndRow($j++, $i, '№');

		foreach ($formFields as $field) {
			if ($field['type'] === 'header'
				|| $field['type'] === 'text'
			) {
				continue;
			}

			$sheet->setCellValueByColumnAndRow($j++, $i, $field['label']);
		}
	}

	/**
	 * @param int   $i Passed by reference
	 * @param int   $j Passed by reference AND will be incremented
	 * @param array $field Must contain $field['value'] of array|int
	 * @return void
	 */
	private function writeCell(int &$i, int &$j, array $field)
	{
		$sheet = $this->spreadsheet->getActiveSheet();

		if (!is_array($field['value'])) {
            $sheet->setCellValueByColumnAndRow(
                ++$j, $i, $field['value']
            );

			return;
		}

        if ($field['value']['n0']) {
            $sheet->setCellValueByColumnAndRow(
                ++$j, $i,
                implode(
                    ',',
                    array_map(
                        fn($file) => 'https://new.vyatsu.ru'
                        . $this->makePrivateLink($file['VALUE']['tmp_name']) ?: 'N',
                        $field['value']
                    )
                )
            );
            return;
        }

        $sheet->setCellValueByColumnAndRow(
            ++$j, $i,
            implode(
                ',',
                array_map(
                    fn($value) => $value ?: 'N',
                    $field['value']
                )
            )
        );
	}

    private function makePrivateLink(string $link): string
    {
        if (stripos($link, '/upload/iblock/') !== false) {
            return $link;
        }

        return '/download_files/?FILENAME=' . str_replace(
                [
                    '/upload/var/www/html/shared/upload/download/private',
                    '/var/www/html/vyatsu_new/upload/download/private'
                ], ['', ''], $link
            );
    }


}
