<?php

namespace Vyatsu\Events\Views\Results;

use \Vyatsu\Events\Application\EventResult;
use \Vyatsu\Events\Views\Results\Course;
use \Vyatsu\Events\Utils\Excel;

class Event implements \Vyatsu\Events\Interfaces\IRenderable
{
    const FULL_PATH_TO_EXCELS = '/var/www/html/vyatsu_new/events_registration/new/xls/';
    const SHORT_PATH_TO_EXCELS = '/events_registration/new/xls/';

    private EventResult $result;
    private string $excelFile = '';

	public function __construct(EventResult $result)
	{
        $this->result = $result;
	}

	public function render(): void
	{
		global $APPLICATION;

		$APPLICATION->SetPageProperty("tags", "Мероприятия, Регистрация, ВятГУ");
		$APPLICATION->SetPageProperty("keywords", "Регистрация на мероприятия ВятГУ");
		$APPLICATION->SetPageProperty("description", "Регистрация на мероприятия");
		$APPLICATION->SetTitle($this->result->getName());
		$APPLICATION->AddChainItem($this->result->getName());

        if (filter_input(
            INPUT_GET, 'make_excel', FILTER_VALIDATE_BOOLEAN
        )) {
            $excel = new Excel();

            $filename = date('Y-m-d') . '_id-' . $this->result->getId() . '.xlsx';

            $excel->makeExcel(
                $this->result->getFormFields()['fields'],
                $this->result->findFormResults(),
	            static::FULL_PATH_TO_EXCELS . $filename
            );

            $this->excelFile = static::SHORT_PATH_TO_EXCELS . $filename;
        }


		$this->renderEvent();
	}

	public static function renderError(string $message)
	{
		global $APPLICATION;

		$APPLICATION->SetPageProperty("tags", "Мероприятия, Регистрация, ВятГУ");
		$APPLICATION->SetPageProperty("keywords", "Регистрация на мероприятия ВятГУ");
		$APPLICATION->SetPageProperty("description", "Регистрация на мероприятия");
		$APPLICATION->SetTitle('Отказано в доступе');
		$APPLICATION->AddChainItem('Регистрация на мероприятия');

        ?>
        <link rel="stylesheet" href="/events_registration/new/assets/css/styles.css">
        <link rel="stylesheet" href="/events_registration/new/assets/css/notification.css">
        <link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH ?>/assets/css/modal-image.css">

        <div class="container">
            <div class="title">
                <?= $message ?>
            </div>
        </div>
        <?php

    }

	private function renderEvent(): void
	{
		?>
		<link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH ?>/assets/css/declarations.min.css">
        <link rel="stylesheet" href="/events_registration/new/assets/css/styles.css">
        <link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH ?>/assets/css/modal-image.css">
		<script type="module" src="/events_registration/new/assets/js/events_main.js"></script>

		<div class="container">
			<div class="title-event">
				<?= $this->result->getName() ?>
			</div>

            <?php $this->printExcelButtons() ?>

            <div style="overflow-x:auto;">
                <?php $this->printTable() ?>
            </div>
		</div>

		<?php
	}

	private function printExcelButtons()
	{
		if (!$this->excelFile) { ?>
            <a class="button-record-event make-excel"
               href="?make_excel=true&id=<?= $this->result->getId() ?>"
            >
                Сделай мне эксель файл, пожалуйста
            </a>
			<?php
		} else { ?>
            <a class="button-record-event"
               href="<?= $this->excelFile ?>"
            >
                А теперь дай скачать
            </a>
			<?php
		}
    }

	private function printTable(): void
	{ ?>
        <table class="documents-table">

            <?php
            $this->printTableHead();
            $this->printTableBody();
            ?>

        </table>
       <?php
    }

	private function printTableHead(): void
	{
        ?>
        <thead>
        <tr>
            <th>№</th>
            <th>Номер записи</th>
            <th title="Если был авторизирован, иначе пустота">
                Пользователь
            </th>
			<?php
			foreach ($this->result->getFormFields()['fields'] as $field) {
				if ($field['type'] === 'header'
					|| $field['type'] === 'text'
				) {
					continue;
				}
				echo "<th>{$field['label']}</th>";
			}

            ?>
        </tr>
        </thead>
       <?php
    }

	private function printTableBody()
	{ ?>
        <tbody>
		<?php
        $course = new Course;
        $arCourse = $course->search($this->result->getId());
        //PR($arCourse);
        if (count($arCourse)===1) {
            $tCourse = reset($arCourse);
            $arCertifications = $course->GetCertification($tCourse['COURSE_ID']);
        } else { ?>
            <h2>Курс у мероприятия не один</h2>
            <?php die();
        }
        //PR($arCertifications);
		$formFields = $this->result->getFormFields();
        $n = 1;
		foreach ($this->result->findFormResults() as $formRes) {
			$userId = $formRes->getUserId();

			$uData = [];
			if ($userId > 1) {
				$uData = \CUser::GetByID($userId)->Fetch();
			} ?>

            <tr id="<?= $formRes->getId() ?>">
                <td><?= $n ?></td>
                <td><?= $formRes->getId() ?></td>

				<?php
				if ($uData) {
					?>
                    <td title="<?= "login: {$uData['LOGIN']}, id: {$uData['ID']}" ?>">
						<?= ($uData['SECOND_NAME'] ?? $uData['NAME'] . ' ' . $uData['LAST_NAME'])
						. "<br>({$uData['UF_GROUP_NAME']})" ?>
                    </td>
					<?php
				} else {
					echo "<td></td>";
				}

				foreach ($formRes->getResult() as $index => $field) {
					if (!is_array($field['value'])) {
                        if (is_bool($field['value'])) {
                            echo "<td>" . ($field['value'] ? 'Да' : 'Нет') . "</td>";
                            continue;
                        }

						echo "<td>" . ($field['value'] ?: 'N') . "</td>";

						continue;
					}

					echo "<td>";
                    if ($field['value']['n0']) {
                        foreach ($field['value'] as $file) {
                            $file = $file['VALUE'];
                            echo "<a href=\"" . $this->makePrivateLink($file['tmp_name']) . "\">{$file['name']}</a>";
                        }
                        continue;
                    }
					foreach ($field['value'] as $valIndex => $value) {
						echo "<div><span style='color: #3e3e3e'>"
                            . $formFields[$index]['options'][$valIndex]['label']
                            . '</span> - '
                            . ($value ?: 'N')
                            . "</div>";
					}
					echo "</td>";
				}
				?>
                
            </tr>
			<?php
            $n++;
		}?>
        </tbody>
		<?php
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
