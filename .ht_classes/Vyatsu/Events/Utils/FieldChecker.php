<?php

namespace Vyatsu\Events\Utils;

use \Vyatsu\Events\RuntimeExceptions\FieldCheckerException;

class FieldChecker
{
    public static array $acceptableTypes = [
        'application/msword', 'text/plain', 'image/jpeg', 'image/png',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/pdf',
    ];

    public static function input(array $field): array
    {
        $value = static::getValue($field['name']);

        if ($field['is_required'] && !$value) {
            throw new FieldCheckerException(
                $field,
                'Не заполнено значение поля "' . $field['label'] . '"'
            );
        }

        return ['name' => $field['name'], 'value' => $value];
    }

    public static function file(array $field): array
    {
        if ($field['is_multiple']) {
            $value = self::makeUploadFilesArr($_FILES[$field['name']]);
        } else {
            $value = self::makeUploadFileArr($_FILES[$field['name']]);
        }

        if ($field['is_required'] && !$value) {
            throw new FieldCheckerException(
                $field,
                'Не заполнено значение поля "' . $field['label'] . '"'
            );
        }

        return ['name' => $field['name'], 'value' => $value];
    }

    public static function checkbox(array $field): array
    {
        $values = static::getValue(
            $field['name'],
            FILTER_SANITIZE_SPECIAL_CHARS,
            FILTER_REQUIRE_ARRAY
        );

        $result = ['name' => $field['name'], 'value' => []];

        foreach ($field['options'] as $index => $option) {
            if ($option['is_required'] && !$values[$index]) {
                throw new FieldCheckerException(
                    $field,
                    'Не установлена галка у "' . $option['label'] . '"'
                );
            }

            $result['value'][] = $values[$index]
                ? $option['value']
                : false;
        }

        return $result;
    }

    public static function radio(array $field): array
    {
        $value = static::getValue($field['name']);

        if ($field['is_required']
            && (!$value || $value === 'undefined')
        ) {
            throw new FieldCheckerException(
                $field,
                'Ничего не выбрано в "' . $field['label'] . '"'
            );
        }

        return ['name' => $field['name'], 'value' => $value];
    }

    public static function select(array $field): array
    {
        $filterOptions = $field['is_multiple']
            ? FILTER_REQUIRE_ARRAY
            : 0;

        $value = static::getValue(
            $field['name'],
            FILTER_SANITIZE_SPECIAL_CHARS,
            $filterOptions
        );

        if ($field['is_required'] && !$value) {
            throw new FieldCheckerException(
                $field,
                'Ничего не выбрано в "' . $field['label'] . '"'
            );
        }

        return ['name' => $field['name'], 'value' => $value];
    }

    private static function getValue(string $name,
                                     int    $filter = FILTER_SANITIZE_SPECIAL_CHARS,
                                     int    $options = 0
    )
    {
        return filter_input(
            INPUT_POST, $name, $filter, $options
        );
    }

    private static function makeUploadFileArr(array $file): array
    {
        $uploadDirectory = 'download/private/events_registration';
        $filesArr = [];

        self::checkFileType($file['type'], $file['name']);
        self::checkFileSize($file['size'], $file['name']);

        $fileID = \CFile::SaveFile($file, $uploadDirectory);

        if ($fileID < 0) {
            throw new FieldCheckerException("Не удалось сохранить файл {$file['name']}");
        }

        $filesArr["n0"] = [
            "VALUE" => \CFile::MakeFileArray($fileID)
        ];

        return $filesArr;
    }

    private static function makeUploadFilesArr(array $files): array
    {
        $uploadDirectory = 'download/private/events_registration';
        $filesArr = [];

        foreach ($files['name'] as $index => $fileName) {
            self::checkFileType($files['type'][$index], $fileName);
            self::checkFileSize($files['size'][$index], $fileName);
        }

        foreach ($files['name'] as $index => $fileName) {
            $fileID = \CFile::SaveFile([
                'name' => $fileName,
                'type' => $files['type'][$index],
                'tmp_name' => $files['tmp_name'][$index],
                'size' => $files['size'][$index],
            ],
                $uploadDirectory
            );

            if ($fileID < 0) {
                throw new FieldCheckerException("Не удалось сохранить файл $fileName");
            }

            $filesArr["n$index"] = [
                "VALUE" => \CFile::MakeFileArray($fileID)
            ];
        }

        return $filesArr;
    }

    private static function checkFileType(string $fileMimeType, string $fileName): void
    {
        if (!in_array($fileMimeType, static::$acceptableTypes)) {
            throw new FieldCheckerException(
                "Файл $fileName имеет недопустимое расширение: ." . pathinfo($fileName)['extension']
            );
        }
    }

    private static function checkFileSize(int $fileSize, $fileName): void
    {
        if ($fileSize > MAX_FILE_SIZE) {
            throw new FieldCheckerException("Файл $fileName превысил максимально допустимый размер: "
                . MAX_FILE_SIZE / 1048576 . "MB");
        }
    }

}
