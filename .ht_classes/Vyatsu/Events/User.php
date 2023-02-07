<?php

namespace Vyatsu\Events;

class User
{
	/**
	 * Every parameter is optional BUT
	 * if no parameters provided it takes info about current user.
	 * The login is preferred
	 * @param string $login [optional]  Login of desired user
	 * @param int    $id    [optional]  ID of desired user
	 * @return array
	 */
	public static function getUserInfo(string $login = '', int $id = 0): array
	{
		$login = trim($login) ?: static::getLogin($id);

 		if (!$login) {
			return [];
		}

		return mb_stripos($login, 'usr') === false
            ? static::reformatStudInfo(static::getStudInfo($login))
            : static::reformatEmployeeInfo(static::getEmployeeInfo($login));
	}

	/**
	 * @param int $id if not provided, current user login returned otherwise login of provided user
	 * @return string
	 */
	private static function getLogin(int $id = 0): string
	{
		if ($id === 0) {
			global $USER;
			return $USER->GetLogin() ?? '';
		}

		return \CUser::GetById($id)->Fetch()['LOGIN'] ?? '';
	}

	public static function getEmployeeInfo(string $login): array
	{
		$email = $login . '@vyatsu.ru';

		$tmp = \curl_get_array(
            \ApiLinks::PROGRAMMER_API . "/public/api/sotrudnikinfo_v1", [
				"login" => $email
			], []
		);

		if (static::checkInfo($tmp)) {
			return $tmp;
		}

		return [];
	}

	public static function getStudInfo(string $login): array
	{
		$studID = (int)preg_replace('/[^0-9]/', '', $login);

		$tmp = \curl_get_array(
            \ApiLinks::PROGRAMMER_API . "/public/api/studentinfo_v3",
			[
				"student_id" => $studID,
				"login" => $login,
			],
			[]
		);

		if (static::checkInfo($tmp)) {
			$result["studentinfo_v3"] = $tmp;
		} else {
            return [];
        }

		$portal      = $result['studentinfo_v3']['portal'][0];
		$infoService = $result['studentinfo_v3']['info_service'][0];

		return compact('portal', 'infoService');
	}

	public static function reformatStudInfo(array $userInfo): array
	{
		return [
			'fio' => $userInfo['infoService']['fio'],

			'last_name' => $userInfo['infoService']['fam'],
			'first_name' => $userInfo['infoService']['nam'],
			'middle_name' => $userInfo['infoService']['otch'],
            'birthdate' => self::reformatDate($userInfo['infoService']['birthdate'] ?? ''),
            'age' => self::getAge($userInfo['infoService']['birthdate'] ?? ''),

			'group_name' => $userInfo['infoService']['group_name'],
			'faculty_short' => $userInfo['infoService']['podr_code'],
			'faculty_full'  => $userInfo['infoService']['podr_name'],

			'course'=> $userInfo['infoService']['kurs'],
			'is_last_course' => $userInfo['portal']['prop_last_kurs'],

			'email' => $userInfo['infoService']['stud_email'],
			'phone' => trim(explode(
				"Телефон мобильный:",
				$userInfo['infoService']['str_phone']
			)[1]),

			'direction_code' => $userInfo['infoService']['direction_code'],
			'direction_name' => $userInfo['infoService']['direction_name'],

			'profile_name'  => $userInfo['infoService']['profile_name'],
			'edu_form'  => $userInfo['portal']['form_ob_type_name'],
			'form_ob'   => $userInfo['portal']['form_ob_type_name'],
			'level_name'    => $userInfo['infoService']['level_name'],
			'tech_name' => $userInfo['portal']['form_ob_tech_name'],

            'stud_type' => $userInfo['portal']['stud_type'],
            'is_pvz' => $userInfo['portal']['stud_type'] === 'Полное возмещение затрат',

            'contract' => $userInfo['infoService']['dogovor'],

            'is_in_hostel' => $userInfo['portal']['obch_num'] > 0,

            'address_reg' => $userInfo['api']['infoService']['address_reg'] ?? '',
            'address_resident' => $userInfo['api']['infoService']['address_resident'] ?? '',
		];
	}

	public static function reformatEmployeeInfo(array $userInfo): array
	{
		return [
			'tabnum' => $userInfo['tabnum'],
			'fio' => $userInfo['fio'],
			'birthdate' => $userInfo['birthday'],
			'pasp' => [
				'ser' => $userInfo['pasp_ser'],
				'number' => $userInfo['pasp_num'],
			],
            'pasp_ser' => $userInfo['pasp_ser'],
            'pasp_number' => $userInfo['pasp_num'],
			'snils' => $userInfo['snils'],
			'inn' => $userInfo['inn'],
            'age' => self::getEmployeeAge($userInfo['birthday'] ?? ''),
		];
	}

	private static function checkInfo(?array $info = []): bool
	{
		return !empty($info) && !key_exists("error", $info);
	}

    public static function reformatDate(string $date)
    {
        return date('d.m.Y', strtotime($date));
    }

    public static function getAge(string $date): int
    {
        if (!$date) {
            return 0;
        }

        $birthDate = explode('-', explode('T', $date)[0]);

        return (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md")
            ? ((date("Y") - $birthDate[0]) - 1)
            : (date("Y") - $birthDate[0]));
    }

    public static function getEmployeeAge(string $date): int
    {
        if (!$date) {
            return 0;
        }

        $date = explode('.', $date);

        return (date("md", date("U", mktime(0, 0, 0, $date[1], $date[0], $date[2]))) > date("md")
            ? ((date("Y") - $date[2]) - 1)
            : (date("Y") - $date[2]));
    }

}
