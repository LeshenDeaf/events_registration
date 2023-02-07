<?php

namespace Vyatsu\Events\Views\Results;
use CModule;
use CCourse;
use CCertification;
class Course
{
    public function search($EVENT_ID) 
    {
        if (CModule::IncludeModule("learning")) {
            $res = CCourse::GetList(
                Array("SORT"=>"ASC"), 
                Array("ACTIVE" => "Y", "CHECK_PERMISSIONS" => "Y", )
            );
            $n = 0;
            while ($arFields = $res->GetNext())
            {
                $UF_Fields[$n] = $this->CourseGetUFByID($arFields["LESSON_ID"], $EVENT_ID);
                $UF_Fields[$n]["COURSE_ID"] = $arFields["ID"];
                $n++;
            }
            foreach ($UF_Fields as $key => $value) {
                if (!empty($value['UF_EVENT'])) $result[] = $value;
            }
        }
        return $result;
    }
    public static function CourseGetUFByID($VALUE_ID, $UF_EVENT)
    {
        global $DB;
        $where = "F.VALUE_ID=" . $VALUE_ID . " AND F.UF_EVENT=" . $UF_EVENT;
        $strSql = "
            SELECT
                F.*
            FROM b_uts_learning_lessons as F
            WHERE 
                $where
            ";
        $res = $DB->Query($strSql);
        while ($row = $res->Fetch())
        {
            $result[] = $row;		
        }
        return $result[0];
    }
    public function GetCertification($COURSE_ID) 
    {
        $res = CCertification::GetList(
            Array("SUMMARY" => "DESC", "USER_NAME"=>"ASC"), 
            Array("ACTIVE" => "Y", "COURSE_ID" => $COURSE_ID, "CHECK_PERMISSIONS" => "Y")
        );
    
        while ($arFileds = $res->GetNext())
        {
            $arCertification[$arFileds['USER_ID']] = $arFileds;
        }
        return $arCertification;
    }
}