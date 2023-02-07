<?php

namespace Vyatsu\Events\Application;

/**
 * @deprecated Because form render makes front-end
 */
class Form
{
	private array $formFields;
	private array $formFieldsRaw;
	private int $agreementId;

    public static array $ar;

	public function __construct(array $formFields, int $agreementId = 0)
	{
		$this->formFieldsRaw = $formFields;
        $this->agreementId = $agreementId;
	}

	public function getFormFields(): array
	{
		return $this->formFields;
	}


	public function getFormFieldsRaw(): array
	{
		return $this->formFieldsRaw;
	}

	/**
	 * @param Vyatsu\Events\Interfaces\IStringifiable[] $formFieldsRaw
	 * @return array
	 */
    private function generateFormFields(array $formFieldsRaw): array
    {
        $formFields = [];

        foreach ($formFieldsRaw as $field)
        {
            $formFields[] = $this->choose($field);
        }

        $this->formFields = $formFields;

        return $formFields;
    }

    private function choose(array $field): \Vyatsu\Events\Interfaces\IStringifiable
    {
        if (static::$ar) {
            return static::$ar[$field['type']];
        }

        static::$ar = [
	        'text' => static fn ($field) =>
                new Vyatsu\Events\FormFields\Text(
                    $field['text']
                ),
	        'input' => static fn ($field) =>
                new Vyatsu\Events\FormFields\FormField(
                    $field['label'],
                    $field['name'],
                    $field['is_required'],
                    'text',

                ),
	        'checkbox' => static fn ($field) =>
                new Vyatsu\Events\FormFields\Groups\CheckboxGroup(
                    $field['label'],
                    $field['name'],
                    $field['options']
                ),
	        'radio' => static fn ($field) =>
                new Vyatsu\Events\FormFields\Groups\RadioGroup(
                    $field['label'],
                    $field['name'],
	                $field['options']
                ),
            'select' => static fn ($field) =>
                new Vyatsu\Events\FormFields\Select(
                    $field['label'],
                    $field['name'],
	                $field['options']

                ),
        ];

	    return static::$ar[$field['type']];
    }

    private function autocomplete(string $fieldName): string
    {
        return '';
    }

	private function includeCaptcha(): void
	{
		global $APPLICATION;

		$code = $APPLICATION->CaptchaGetCode();
		?>
		<div>
			<input type="hidden" name="confirmorder" id="confirmorder" value="Y">
			<input type="hidden" name="profile_change" id="profile_change" value="N">
			<input type="hidden" name="is_ajax_post" id="is_ajax_post" value="Y">

			<div class="captcha-holder">
				<label for="object-20">Введите символы:</label>
				<?// Изображение капчи?>
				<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$code;?>" alt="CAPTCHA" width="110" height="33" class="captcha_pic" />
				<?// Обновление капчи?>
				<a href="#" rel="nofollow" class="update-captcha">Обновить</a><br>
				<?// Скрытое поле капчи?>
				<input type="hidden" name="captcha_sid" value="<?=$code;?>" />
				<?// Поле для ввода капчи пользователем?>
				<input type="text" id="object-20" class="form-text input-block__field input-block__field_input" name="captcha_word" value="" required/>
			</div>
		</div>
		<?php
	}

	private function includeAgreement(): void
	{
		global $APPLICATION;

		echo '<div>';
		$APPLICATION->IncludeComponent(
			"bitrix:main.userconsent.request",
			"", [
				"AUTO_SAVE" => "Y",
				"COMPOSITE_FRAME_MODE" => "A",
				"COMPOSITE_FRAME_TYPE" => "AUTO",
				"ID" => "$this->agreementId",
				"IS_CHECKED" => "N",
				"IS_LOADED" => "N"
			]
		);
		echo '</div>';
	}

	public function render(): void
	{
		echo '<form method="post"><div class="application_form">';

		foreach ($this->formFields as $formField) {
			echo $formField->stringify();
		}

		if ($this->agreementId) {
			$this->includeAgreement();
		}

		$this->includeCaptcha();

		echo '<div><br><label><button class="btn btn_primary btn_size_s btn_disabled" value="submit" name="send_submit_button" type="submit">Отправить заявление</button></label></div></div></form>';
	}

}
