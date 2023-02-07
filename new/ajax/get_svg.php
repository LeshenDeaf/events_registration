<?php


define('STOP_STATISTICS', true);

require_once $_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_before.php';

\CModule::IncludeModule("iblock");

$APPLICATION->RestartBuffer();

header('Content-type: image/svg+xml');


function response()
{
    echo '<?xml version="1.0" standalone="no"?>
    <!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"
    "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">';

    $head = filter_input(INPUT_POST, 'head', FILTER_SANITIZE_SPECIAL_CHARS);

    $text = filter_input(INPUT_POST, 'text', FILTER_SANITIZE_SPECIAL_CHARS);

    $contacts = filter_input(
        INPUT_POST,
        'contacts',
			FILTER_SANITIZE_SPECIAL_CHARS,
        FILTER_REQUIRE_ARRAY
    ) ?? [];

    $r = rand();
    ?>

    <svg class="card-event" viewBox="0 0 606 606" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <g clip-path="url(#clip0_1154_23<?= $r ?>)">
            <rect width="606" height="606" fill="white"/>
            <path d="M-252.706 534.993c-79.764-55.514-207.753-106.506-383.966-152.977-176.212-46.47-230.523-167.754-162.931-363.85 67.592-196.095 190.832-287.92 369.72-275.474 178.889 12.446 237.237-160.21 265.575-333.162C-135.97-763.422-6.56-795.643 133.394-871.941c139.954-76.297 274.877-61.248 404.77 45.15C668.057-720.393 726.397-565.76 713.185-362.892c-13.212 202.869-17.799 409.668-13.76 620.398 4.039 210.73-99.763 266.844-311.406 168.341-211.643-98.503-351.4-91.059-419.272 22.331-67.87 113.39-141.689 142.328-221.453 86.815Z" fill="#7DAAFE"/>
            <path d="M-256.417 415.485c-71.509-65.807-191.41-133.668-359.703-203.581-168.294-69.913-205.674-197.436-112.141-382.567 93.533-185.132 214.523-191.361 403.63-222.851 165.741-27.599 285.733-59.702 337.24-227.221 51.505-167.519 155.107-248.806 304.106-305.441 148.999-56.636 280.64-23.447 394.923 99.566C825.92-703.597 862.775-542.487 822.203-343.278c-40.573 199.209-73.131 403.48-97.676 612.815-24.545 209.335-134.992 250.87-331.34 124.604C196.839 267.876 57.361 256.319-25.245 359.47c-82.606 103.15-159.664 121.822-231.172 56.015Z" fill="#00CEC9"/>
            <path d="M-256.405 415.472c-71.513-65.802-191.42-133.653-359.718-203.553-168.299-69.9-205.689-197.42-112.17-382.559 93.519-185.139 214.508-191.377 403.613-222.881 165.739-27.612 285.729-59.724 337.222-227.247C64.035-788.291 167.63-869.586 316.625-926.233c148.995-56.647 280.639-23.468 394.93 99.536 114.292 123.004 151.159 284.112 110.602 483.324-40.557 199.212-73.1 403.486-97.629 612.823-24.529 209.336-134.973 250.879-331.33 124.629-196.358-126.25-335.836-137.797-418.434-34.64-82.599 103.157-159.655 121.835-231.169 56.033Z" fill="url(#paint0_linear_1154_23<?= $r ?>)"/>
            <g opacity=".2"><mask id="mask0_1154_23<?= $r ?>" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="546" y="531" width="100" height="100"><path fill="url(#pattern0)" d="M546 531h100v100H546z"/></mask><g mask="url(#mask0_1154_23<?= $r ?>)"><path fill="#4080F5" d="M544.5 543H659v60.5H544.5z"/></g></g>
            <mask id="mask1_1154_23<?= $r ?>" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="326" y="-75" width="150" height="150"><rect x="326" y="-75" width="150" height="150" fill="url(#pattern1)"/></mask>
            <g mask="url(#mask1_1154_23<?= $r ?>)"><rect x="311" y="-33.75" width="171.75" height="90.75" fill="white"/></g>
            <mask id="mask2_1154_23<?= $r ?>" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="508" y="303" width="170" height="170"><rect x="508" y="303" width="170" height="170" fill="url(#pattern2)"/></mask>
            <g mask="url(#mask2_1154_23<?= $r ?>)"><rect x="519.05" y="303" width="194.65" height="170" fill="white"/></g>

            <text x="30" y="56" fill="#fff" font-family="Montserrat" font-size="24" font-weight="400" stroke="#fff">
                <tspan x="30" dy="1.2em"><?= wordwrap($head, 70, "</tspan><tspan x=\"30\" dy=\"1.2em\">") ?></tspan>
            </text>
            <?php
            if ($contacts) {?>
                <text x="43" y="450" fill="#4080F5" font-family="Montserrat" font-size="24" font-weight="400" stroke="#4080F5">
                    <tspan x="43" dy="1.2em">По всем вопросам:</tspan>
                </text>

                <text x="43" y="490" fill="#4080F5" font-family="Montserrat" font-size="24" font-weight="400" stroke="#4080F5">
                    <?php foreach ($contacts as $contact) { ?><tspan x="43" dy="1.2em"><?= $contact ?></tspan><?php } ?>
                </text>
                <?php
            }
            ?>
            <text x="43" y="100" fill="white" font-family="Montserrat" font-size="48" font-weight="800" stroke="white">
                <tspan x="43" dy="1.2em">
                    <?= wordwrap(mb_strimwidth($text, 0, 50,'...'), 35, "</tspan><tspan x=\"43\" dy=\"1.2em\">") ?>
                </tspan>
            </text>

            <rect x="-24" y="75" width="48" height="2" rx="1" fill="white"/>
            <g opacity="0.1">
                <mask id="mask3_1154_23<?= $r ?>" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="242" y="-120" width="500" height="500"><rect x="242" y="-120" width="500" height="500" fill="url(#pattern3)"/></mask>
                <g mask="url(#mask3_1154_23<?= $r ?>)"><rect x="2" y="-320" width="1090" height="940" fill="white"/></g>
            </g>
        </g>
        <defs>
            <pattern id="pattern0" patternContentUnits="objectBoundingBox" width="1" height="1">
                <use xlink:href="#image0_1154_23<?= $r ?>" transform="scale(0.000244141)"/>
            </pattern>
            <pattern id="pattern1" patternContentUnits="objectBoundingBox" width="1" height="1">
                <use xlink:href="#image0_1154_23<?= $r ?>" transform="scale(0.000244141)"/>
            </pattern>
            <pattern id="pattern2" patternContentUnits="objectBoundingBox" width="1" height="1">
                <use xlink:href="#image1_1154_23<?= $r ?>" transform="scale(0.000244141)"/>
            </pattern>
            <pattern id="pattern3" patternContentUnits="objectBoundingBox" width="1" height="1">
                <use xlink:href="#image2_1154_23<?= $r ?>" transform="scale(0.00465116)"/>
            </pattern>
            <linearGradient id="paint0_linear_1154_23<?= $r ?>" x1="484" y1="187" x2="484" y2="453" gradientUnits="userSpaceOnUse">
                <stop stop-color="#4080F5" offset="0"/>
                <stop offset="1" stop-color="#7DAAFE"/>
            </linearGradient>
            <clipPath id="clip0_1154_23<?= $r ?>">
                <rect width="606" height="606" fill="white"/>
            </clipPath>
            <image id="image0_1154_23<?= $r ?>" width="4096" height="4096" xlink:href="/events_registration/new/assets/dots.png" />
            <image id="image1_1154_23<?= $r ?>" width="4096" height="4096" xlink:href="/events_registration/new/assets/many_dots.png"/>
            <image id="image2_1154_23<?= $r ?>" width="215" height="215" xlink:href="/events_registration/new/assets/s_logo.png"/>
        </defs>
    </svg>

    <?php
}

if ($_POST['head'] || $_POST['text']) {
    response();
}

