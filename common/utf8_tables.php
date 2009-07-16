<?php
/*
--------------------------------------------------------------------------
FSBoard - Free, open-source message board system.
Copyright (C) 2007 Fiona Burrows (fiona@fsboard.net)

FSBoard is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License.
See gpl.txt for a full copy of this license.
--------------------------------------------------------------------------
*/

/**
 * UT8 character tables
 * 
 * These arrays are used with the UTF-8 friendly versions of
 * strtoupper() and strtolower(). Only a few languages actually
 * have a concept of case, this covers them. 
 * 
 * Original tables taken from code by Niels Leenheer & Andy Matsubara,
 * released under GPL.
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


$UTF8_TABLES['strtolower'] = array(
        "ï¼º" => "ï½š",        "ï¼¹" => "ï½™",        "ï¼¸" => "ï½˜",
        "ï¼·" => "ï½—",        "ï¼¶" => "ï½–",        "ï¼µ" => "ï½•",
        "ï¼´" => "ï½”",        "ï¼³" => "ï½“",        "ï¼²" => "ï½’",
        "ï¼±" => "ï½‘",        "ï¼°" => "ï½",        "ï¼¯" => "ï½",
        "ï¼®" => "ï½Ž",        "ï¼­" => "ï½",        "ï¼¬" => "ï½Œ",
        "ï¼«" => "ï½‹",        "ï¼ª" => "ï½Š",        "ï¼©" => "ï½‰",
        "ï¼¨" => "ï½ˆ",        "ï¼§" => "ï½‡",        "ï¼¦" => "ï½†",
        "ï¼¥" => "ï½…",        "ï¼¤" => "ï½„",        "ï¼£" => "ï½ƒ",
        "ï¼¢" => "ï½‚",        "ï¼¡" => "ï½",        "â„«" => "Ã¥",
        "â„ª" => "k",          "â„¦" => "Ï‰",         "á¿»" => "á½½",
        "á¿º" => "á½¼",        "á¿¹" => "á½¹",        "á¿¸" => "á½¸",
        "á¿¬" => "á¿¥",        "á¿«" => "á½»",        "á¿ª" => "á½º",
        "á¿©" => "á¿¡",        "á¿¨" => "á¿ ",        "á¿›" => "á½·",
        "á¿š" => "á½¶",        "á¿™" => "á¿‘",        "á¿˜" => "á¿",
        "á¿‹" => "á½µ",        "á¿Š" => "á½´",        "á¿‰" => "á½³",
        "á¿ˆ" => "á½²",        "á¾»" => "á½±",        "á¾º" => "á½°",
        "á¾¹" => "á¾±",        "á¾¸" => "á¾°",        "á½¯" => "á½§",
        "á½®" => "á½¦",        "á½­" => "á½¥",        "á½¬" => "á½¤",
        "á½«" => "á½£",        "á½ª" => "á½¢",        "á½©" => "á½¡",
        "á½¨" => "á½ ",        "á½Ÿ" => "á½—",        "á½" => "á½•",
        "á½›" => "á½“",        "á½™" => "á½‘",        "á½" => "á½…",
        "á½Œ" => "á½„",        "á½‹" => "á½ƒ",        "á½Š" => "á½‚",
        "á½‰" => "á½",        "á½ˆ" => "á½€",        "á¼¿" => "á¼·",
        "á¼¾" => "á¼¶",        "á¼½" => "á¼µ",        "á¼¼" => "á¼´",
        "á¼»" => "á¼³",        "á¼º" => "á¼²",        "á¼¹" => "á¼±",
        "á¼¸" => "á¼°",        "á¼¯" => "á¼§",        "á¼®" => "á¼¦",
        "á¼­" => "á¼¥",        "á¼¬" => "á¼¤",        "á¼«" => "á¼£",
        "á¼ª" => "á¼¢",        "á¼©" => "á¼¡",        "á¼¨" => "á¼ ",
        "á¼" => "á¼•",        "á¼œ" => "á¼”",        "á¼›" => "á¼“",
        "á¼š" => "á¼’",        "á¼™" => "á¼‘",        "á¼˜" => "á¼",
        "á¼" => "á¼‡",        "á¼Ž" => "á¼†",        "á¼" => "á¼…",
        "á¼Œ" => "á¼„",        "á¼‹" => "á¼ƒ",        "á¼Š" => "á¼‚",
        "á¼‰" => "á¼",        "á¼ˆ" => "á¼€",        "á»¸" => "á»¹",
        "á»¶" => "á»·",        "á»´" => "á»µ",        "á»²" => "á»³",
        "á»°" => "á»±",        "á»®" => "á»¯",        "á»¬" => "á»­",
        "á»ª" => "á»«",        "á»¨" => "á»©",        "á»¦" => "á»§",
        "á»¤" => "á»¥",        "á»¢" => "á»£",        "á» " => "á»¡",
        "á»ž" => "á»Ÿ",        "á»œ" => "á»",        "á»š" => "á»›",
        "á»˜" => "á»™",        "á»–" => "á»—",        "á»”" => "á»•",
        "á»’" => "á»“",        "á»" => "á»‘",        "á»Ž" => "á»",
        "á»Œ" => "á»",        "á»Š" => "á»‹",        "á»ˆ" => "á»‰",
        "á»†" => "á»‡",        "á»„" => "á»…",        "á»‚" => "á»ƒ",
        "á»€" => "á»",        "áº¾" => "áº¿",        "áº¼" => "áº½",
        "áºº" => "áº»",        "áº¸" => "áº¹",        "áº¶" => "áº·",
        "áº´" => "áºµ",        "áº²" => "áº³",        "áº°" => "áº±",
        "áº®" => "áº¯",        "áº¬" => "áº­",        "áºª" => "áº«",
        "áº¨" => "áº©",        "áº¦" => "áº§",        "áº¤" => "áº¥",
        "áº¢" => "áº£",        "áº " => "áº¡",        "áº”" => "áº•",
        "áº’" => "áº“",        "áº" => "áº‘",        "áºŽ" => "áº",
        "áºŒ" => "áº",        "áºŠ" => "áº‹",        "áºˆ" => "áº‰",
        "áº†" => "áº‡",        "áº„" => "áº…",        "áº‚" => "áºƒ",
        "áº€" => "áº",        "á¹¾" => "á¹¿",        "á¹¼" => "á¹½",
        "á¹º" => "á¹»",        "á¹¸" => "á¹¹",        "á¹¶" => "á¹·",
        "á¹´" => "á¹µ",        "á¹²" => "á¹³",        "á¹°" => "á¹±",
        "á¹®" => "á¹¯",        "á¹¬" => "á¹­",        "á¹ª" => "á¹«",
        "á¹¨" => "á¹©",        "á¹¦" => "á¹§",        "á¹¤" => "á¹¥",
        "á¹¢" => "á¹£",        "á¹ " => "á¹¡",        "á¹ž" => "á¹Ÿ",
        "á¹œ" => "á¹",         "á¹š" => "á¹›",        "á¹˜" => "á¹™",
        "á¹–" => "á¹—",        "á¹”" => "á¹•",        "á¹’" => "á¹“",
        "á¹" => "á¹‘",         "á¹Ž" => "á¹",        "á¹Œ" => "á¹",
        "á¹Š" => "á¹‹",        "á¹ˆ" => "á¹‰",        "á¹†" => "á¹‡",
        "á¹„" => "á¹…",        "á¹‚" => "á¹ƒ",        "á¹€" => "á¹",
        "á¸¾" => "á¸¿",        "á¸¼" => "á¸½",        "á¸º" => "á¸»",
        "á¸¸" => "á¸¹",        "á¸¶" => "á¸·",        "á¸´" => "á¸µ",
        "á¸²" => "á¸³",        "á¸°" => "á¸±",        "á¸®" => "á¸¯",
        "á¸¬" => "á¸­",        "á¸ª" => "á¸«",        "á¸¨" => "á¸©",
        "á¸¦" => "á¸§",        "á¸¤" => "á¸¥",        "á¸¢" => "á¸£",
        "á¸ " => "á¸¡",        "á¸ž" => "á¸Ÿ",        "á¸œ" => "á¸",
        "á¸š" => "á¸›",        "á¸˜" => "á¸™",        "á¸–" => "á¸—",
        "á¸”" => "á¸•",        "á¸’" => "á¸“",        "á¸" => "á¸‘",
        "á¸Ž" => "á¸",        "á¸Œ" => "á¸",        "á¸Š" => "á¸‹",
        "á¸ˆ" => "á¸‰",        "á¸†" => "á¸‡",        "á¸„" => "á¸…",
        "á¸‚" => "á¸ƒ",        "á¸€" => "á¸",        "Õ–" => "Ö†",
        "Õ•" => "Ö…",          "Õ”" => "Ö„",          "Õ“" => "Öƒ",
        "Õ’" => "Ö‚",          "Õ‘" => "Ö",          "Õ" => "Ö€",
        "Õ" => "Õ¿",          "ÕŽ" => "Õ¾",          "Õ" => "Õ½",
        "ÕŒ" => "Õ¼",          "Õ‹" => "Õ»",          "ÕŠ" => "Õº",
        "Õ‰" => "Õ¹",          "Õˆ" => "Õ¸",          "Õ‡" => "Õ·",
        "Õ†" => "Õ¶",          "Õ…" => "Õµ",          "Õ„" => "Õ´",
        "Õƒ" => "Õ³",          "Õ‚" => "Õ²",          "Õ" => "Õ±",
        "Õ€" => "Õ°",          "Ô¿" => "Õ¯",          "Ô¾" => "Õ®",
        "Ô½" => "Õ­",          "Ô¼" => "Õ¬",          "Ô»" => "Õ«",
        "Ôº" => "Õª",          "Ô¹" => "Õ©",          "Ô¸" => "Õ¨",
        "Ô·" => "Õ§",          "Ô¶" => "Õ¦",          "Ôµ" => "Õ¥",
        "Ô´" => "Õ¤",          "Ô³" => "Õ£",          "Ô²" => "Õ¢",
        "Ô±" => "Õ¡",          "ÔŽ" => "Ô",          "ÔŒ" => "Ô",
        "ÔŠ" => "Ô‹",          "Ôˆ" => "Ô‰",          "Ô†" => "Ô‡",
        "Ô„" => "Ô…",          "Ô‚" => "Ôƒ",          "Ô€" => "Ô",
        "Ó¸" => "Ó¹",          "Ó´" => "Óµ",          "Ó²" => "Ó³",
        "Ó°" => "Ó±",          "Ó®" => "Ó¯",          "Ó¬" => "Ó­",
        "Óª" => "Ó«",          "Ó¨" => "Ó©",          "Ó¦" => "Ó§",
        "Ó¤" => "Ó¥",          "Ó¢" => "Ó£",          "Ó " => "Ó¡",
        "Óž" => "ÓŸ",          "Óœ" => "Ó",          "Óš" => "Ó›",
        "Ó˜" => "Ó™",          "Ó–" => "Ó—",          "Ó”" => "Ó•",
        "Ó’" => "Ó“",          "Ó" => "Ó‘",          "Ó" => "ÓŽ",
        "Ó‹" => "ÓŒ",          "Ó‰" => "ÓŠ",          "Ó‡" => "Óˆ",
        "Ó…" => "Ó†",          "Óƒ" => "Ó„",          "Ó" => "Ó‚",
        "Ò¾" => "Ò¿",          "Ò¼" => "Ò½",          "Òº" => "Ò»",
        "Ò¸" => "Ò¹",          "Ò¶" => "Ò·",          "Ò´" => "Òµ",
        "Ò²" => "Ò³",          "Ò°" => "Ò±",          "Ò®" => "Ò¯",
        "Ò¬" => "Ò­",          "Òª" => "Ò«",          "Ò¨" => "Ò©",
        "Ò¦" => "Ò§",          "Ò¤" => "Ò¥",          "Ò¢" => "Ò£",
        "Ò " => "Ò¡",          "Òž" => "ÒŸ",          "Òœ" => "Ò",
        "Òš" => "Ò›",          "Ò˜" => "Ò™",          "Ò–" => "Ò—",
        "Ò”" => "Ò•",          "Ò’" => "Ò“",          "Ò" => "Ò‘",
        "ÒŽ" => "Ò",          "ÒŒ" => "Ò",          "ÒŠ" => "Ò‹",
        "Ò€" => "Ò",          "Ñ¾" => "Ñ¿",          "Ñ¼" => "Ñ½",
        "Ñº" => "Ñ»",        "Ñ¸" => "Ñ¹",        "Ñ¶" => "Ñ·",
        "Ñ´" => "Ñµ",        "Ñ²" => "Ñ³",        "Ñ°" => "Ñ±",
        "Ñ®" => "Ñ¯",        "Ñ¬" => "Ñ­",        "Ñª" => "Ñ«",
        "Ñ¨" => "Ñ©",        "Ñ¦" => "Ñ§",        "Ñ¤" => "Ñ¥",
        "Ñ¢" => "Ñ£",        "Ñ " => "Ñ¡",        "Ð¯" => "Ñ",
        "Ð®" => "ÑŽ",        "Ð­" => "Ñ",        "Ð¬" => "ÑŒ",
        "Ð«" => "Ñ‹",        "Ðª" => "ÑŠ",        "Ð©" => "Ñ‰",
        "Ð¨" => "Ñˆ",        "Ð§" => "Ñ‡",        "Ð¦" => "Ñ†",
        "Ð¥" => "Ñ…",        "Ð¤" => "Ñ„",        "Ð£" => "Ñƒ",
        "Ð¢" => "Ñ‚",        "Ð¡" => "Ñ",        "Ð " => "Ñ€",
        "ÐŸ" => "Ð¿",        "Ðž" => "Ð¾",        "Ð" => "Ð½",
        "Ðœ" => "Ð¼",        "Ð›" => "Ð»",        "Ðš" => "Ðº",
        "Ð™" => "Ð¹",        "Ð˜" => "Ð¸",        "Ð—" => "Ð·",
        "Ð–" => "Ð¶",        "Ð•" => "Ðµ",        "Ð”" => "Ð´",
        "Ð“" => "Ð³",        "Ð’" => "Ð²",        "Ð‘" => "Ð±",
        "Ð" => "Ð°",        "Ð" => "ÑŸ",        "ÐŽ" => "Ñž",
        "Ð" => "Ñ",        "ÐŒ" => "Ñœ",        "Ð‹" => "Ñ›",
        "ÐŠ" => "Ñš",        "Ð‰" => "Ñ™",        "Ðˆ" => "Ñ˜",
        "Ð‡" => "Ñ—",        "Ð†" => "Ñ–",        "Ð…" => "Ñ•",
        "Ð„" => "Ñ”",        "Ðƒ" => "Ñ“",        "Ð‚" => "Ñ’",
        "Ð" => "Ñ‘",        "Ð€" => "Ñ",        "Ï´" => "Î¸",
        "Ï®" => "Ï¯",        "Ï¬" => "Ï­",        "Ïª" => "Ï«",
        "Ï¨" => "Ï©",        "Ï¦" => "Ï§",        "Ï¤" => "Ï¥",
        "Ï¢" => "Ï£",        "Ï " => "Ï¡",        "Ïž" => "ÏŸ",
        "Ïœ" => "Ï",        "Ïš" => "Ï›",        "Ï˜" => "Ï™",
        "Î«" => "Ï‹",        "Îª" => "ÏŠ",        "Î©" => "Ï‰",
        "Î¨" => "Ïˆ",        "Î§" => "Ï‡",        "Î¦" => "Ï†",
        "Î¥" => "Ï…",        "Î¤" => "Ï„",        "Î£" => "Ïƒ",
        "Î¡" => "Ï",        "Î " => "Ï€",        "ÎŸ" => "Î¿",
        "Îž" => "Î¾",        "Î" => "Î½",        "Îœ" => "Î¼",
        "Î›" => "Î»",        "Îš" => "Îº",        "Î™" => "Î¹",
        "Î˜" => "Î¸",        "Î—" => "Î·",        "Î–" => "Î¶",
        "Î•" => "Îµ",        "Î”" => "Î´",        "Î“" => "Î³",
        "Î’" => "Î²",        "Î‘" => "Î±",        "Î" => "ÏŽ",
        "ÎŽ" => "Ï",        "ÎŒ" => "ÏŒ",        "ÎŠ" => "Î¯",
        "Î‰" => "Î®",        "Îˆ" => "Î­",        "Î†" => "Î¬",
        "È²" => "È³",        "È°" => "È±",        "È®" => "È¯",
        "È¬" => "È­",        "Èª" => "È«",        "È¨" => "È©",
        "È¦" => "È§",        "È¤" => "È¥",        "È¢" => "È£",
        "È " => "Æž",        "Èž" => "ÈŸ",        "Èœ" => "È",
        "Èš" => "È›",        "È˜" => "È™",        "È–" => "È—",
        "È”" => "È•",        "È’" => "È“",        "È" => "È‘",
        "ÈŽ" => "È",        "ÈŒ" => "È",        "ÈŠ" => "È‹",
        "Èˆ" => "È‰",        "È†" => "È‡",        "È„" => "È…",
        "È‚" => "Èƒ",        "È€" => "È",        "Ç¾" => "Ç¿",
        "Ç¼" => "Ç½",        "Çº" => "Ç»",        "Ç¸" => "Ç¹",
        "Ç·" => "Æ¿",        "Ç¶" => "Æ•",        "Ç´" => "Çµ",
        "Ç±" => "Ç³",        "Ç®" => "Ç¯",        "Ç¬" => "Ç­",
        "Çª" => "Ç«",        "Ç¨" => "Ç©",        "Ç¦" => "Ç§",
        "Ç¤" => "Ç¥",        "Ç¢" => "Ç£",        "Ç " => "Ç¡",
        "Çž" => "ÇŸ",        "Ç›" => "Çœ",        "Ç™" => "Çš",
        "Ç—" => "Ç˜",        "Ç•" => "Ç–",        "Ç“" => "Ç”",
        "Ç‘" => "Ç’",        "Ç" => "Ç",        "Ç" => "ÇŽ",
        "ÇŠ" => "ÇŒ",        "Ç‡" => "Ç‰",        "Ç„" => "Ç†",
        "Æ¼" => "Æ½",        "Æ¸" => "Æ¹",        "Æ·" => "Ê’",
        "Æµ" => "Æ¶",        "Æ³" => "Æ´",        "Æ²" => "Ê‹",
        "Æ±" => "ÊŠ",        "Æ¯" => "Æ°",        "Æ®" => "Êˆ",
        "Æ¬" => "Æ­",        "Æ©" => "Êƒ",        "Æ§" => "Æ¨",
        "Æ¦" => "Ê€",        "Æ¤" => "Æ¥",        "Æ¢" => "Æ£",
        "Æ " => "Æ¡",        "ÆŸ" => "Éµ",        "Æ" => "É²",
        "Æœ" => "É¯",        "Æ˜" => "Æ™",        "Æ—" => "É¨",
        "Æ–" => "É©",        "Æ”" => "É£",        "Æ“" => "É ",
        "Æ‘" => "Æ’",        "Æ" => "É›",        "Æ" => "É™",
        "ÆŽ" => "Ç",        "Æ‹" => "ÆŒ",        "ÆŠ" => "É—",
        "Æ‰" => "É–",        "Æ‡" => "Æˆ",        "Æ†" => "É”",
        "Æ„" => "Æ…",        "Æ‚" => "Æƒ",        "Æ" => "É“",
        "Å½" => "Å¾",        "Å»" => "Å¼",        "Å¹" => "Åº",
        "Å¸" => "Ã¿",        "Å¶" => "Å·",        "Å´" => "Åµ",
        "Å²" => "Å³",        "Å°" => "Å±",        "Å®" => "Å¯",
        "Å¬" => "Å­",        "Åª" => "Å«",        "Å¨" => "Å©",
        "Å¦" => "Å§",        "Å¤" => "Å¥",        "Å¢" => "Å£",
        "Å " => "Å¡",        "Åž" => "ÅŸ",        "Åœ" => "Å",
        "Åš" => "Å›",        "Å˜" => "Å™",        "Å–" => "Å—",
        "Å”" => "Å•",        "Å’" => "Å“",        "Å" => "Å‘",
        "ÅŽ" => "Å",        "ÅŒ" => "Å",        "ÅŠ" => "Å‹",
        "Å‡" => "Åˆ",        "Å…" => "Å†",        "Åƒ" => "Å„",
        "Å" => "Å‚",        "Ä¿" => "Å€",        "Ä½" => "Ä¾",
        "Ä»" => "Ä¼",        "Ä¹" => "Äº",        "Ä¶" => "Ä·",
        "Ä´" => "Äµ",        "Ä²" => "Ä³",        "Ä°" => "i",
        "Ä®" => "Ä¯",        "Ä¬" => "Ä­",        "Äª" => "Ä«",
        "Ä¨" => "Ä©",        "Ä¦" => "Ä§",        "Ä¤" => "Ä¥",
        "Ä¢" => "Ä£",        "Ä " => "Ä¡",        "Äž" => "ÄŸ",
        "Äœ" => "Ä",        "Äš" => "Ä›",        "Ä˜" => "Ä™",
        "Ä–" => "Ä—",        "Ä”" => "Ä•",        "Ä’" => "Ä“",
        "Ä" => "Ä‘",        "ÄŽ" => "Ä",        "ÄŒ" => "Ä",
        "ÄŠ" => "Ä‹",        "Äˆ" => "Ä‰",        "Ä†" => "Ä‡",
        "Ä„" => "Ä…",        "Ä‚" => "Äƒ",        "Ä€" => "Ä",
        "Ãž" => "Ã¾",        "Ã" => "Ã½",        "Ãœ" => "Ã¼",
        "Ã›" => "Ã»",        "Ãš" => "Ãº",        "Ã™" => "Ã¹",
        "Ã˜" => "Ã¸",        "Ã–" => "Ã¶",        "Ã•" => "Ãµ",
        "Ã”" => "Ã´",        "Ã“" => "Ã³",        "Ã’" => "Ã²",
        "Ã‘" => "Ã±",        "Ã" => "Ã°",        "Ã" => "Ã¯",
        "ÃŽ" => "Ã®",        "Ã" => "Ã­",        "ÃŒ" => "Ã¬",
        "Ã‹" => "Ã«",        "ÃŠ" => "Ãª",        "Ã‰" => "Ã©",
        "Ãˆ" => "Ã¨",        "Ã‡" => "Ã§",        "Ã†" => "Ã¦",
        "Ã…" => "Ã¥",        "Ã„" => "Ã¤",        "Ãƒ" => "Ã£",
        "Ã‚" => "Ã¢",        "Ã" => "Ã¡",        "Ã€" => "Ã ",
        "Z" => "z",          "Y" => "y",          "X" => "x",
        "W" => "w",          "V" => "v",          "U" => "u",
        "T" => "t",          "S" => "s",          "R" => "r",
        "Q" => "q",          "P" => "p",          "O" => "o",
        "N" => "n",          "M" => "m",          "L" => "l",
        "K" => "k",          "J" => "j",          "I" => "i",
        "H" => "h",          "G" => "g",          "F" => "f",
        "E" => "e",          "D" => "d",          "C" => "c",
        "B" => "b",          "A" => "a",
);


$UTF8_TABLES['strtoupper'] = array(
        "ï½š" => "ï¼º",        "ï½™" => "ï¼¹",        "ï½˜" => "ï¼¸",
        "ï½—" => "ï¼·",        "ï½–" => "ï¼¶",        "ï½•" => "ï¼µ",
        "ï½”" => "ï¼´",        "ï½“" => "ï¼³",        "ï½’" => "ï¼²",
        "ï½‘" => "ï¼±",        "ï½" => "ï¼°",        "ï½" => "ï¼¯",
        "ï½Ž" => "ï¼®",        "ï½" => "ï¼­",        "ï½Œ" => "ï¼¬",
        "ï½‹" => "ï¼«",        "ï½Š" => "ï¼ª",        "ï½‰" => "ï¼©",
        "ï½ˆ" => "ï¼¨",        "ï½‡" => "ï¼§",        "ï½†" => "ï¼¦",
        "ï½…" => "ï¼¥",        "ï½„" => "ï¼¤",        "ï½ƒ" => "ï¼£",
        "ï½‚" => "ï¼¢",        "ï½" => "ï¼¡",        "á¿³" => "á¿¼",
        "á¿¥" => "á¿¬",        "á¿¡" => "á¿©",        "á¿ " => "á¿¨",
        "á¿‘" => "á¿™",        "á¿" => "á¿˜",        "á¿ƒ" => "á¿Œ",
        "á¾¾" => "Î™",         "á¾³" => "á¾¼",        "á¾±" => "á¾¹",
        "á¾°" => "á¾¸",        "á¾§" => "á¾¯",        "á¾¦" => "á¾®",
        "á¾¥" => "á¾­",        "á¾¤" => "á¾¬",        "á¾£" => "á¾«",
        "á¾¢" => "á¾ª",        "á¾¡" => "á¾©",        "á¾ " => "á¾¨",
        "á¾—" => "á¾Ÿ",        "á¾–" => "á¾ž",        "á¾•" => "á¾",
        "á¾”" => "á¾œ",        "á¾“" => "á¾›",        "á¾’" => "á¾š",
        "á¾‘" => "á¾™",        "á¾" => "á¾˜",        "á¾‡" => "á¾",
        "á¾†" => "á¾Ž",        "á¾…" => "á¾",        "á¾„" => "á¾Œ",
        "á¾ƒ" => "á¾‹",        "á¾‚" => "á¾Š",        "á¾" => "á¾‰",
        "á¾€" => "á¾ˆ",        "á½½" => "á¿»",        "á½¼" => "á¿º",
        "á½»" => "á¿«",        "á½º" => "á¿ª",        "á½¹" => "á¿¹",
        "á½¸" => "á¿¸",        "á½·" => "á¿›",        "á½¶" => "á¿š",
        "á½µ" => "á¿‹",        "á½´" => "á¿Š",        "á½³" => "á¿‰",
        "á½²" => "á¿ˆ",        "á½±" => "á¾»",        "á½°" => "á¾º",
        "á½§" => "á½¯",        "á½¦" => "á½®",        "á½¥" => "á½­",
        "á½¤" => "á½¬",        "á½£" => "á½«",        "á½¢" => "á½ª",
        "á½¡" => "á½©",        "á½ " => "á½¨",        "á½—" => "á½Ÿ",
        "á½•" => "á½",        "á½“" => "á½›",        "á½‘" => "á½™",
        "á½…" => "á½",        "á½„" => "á½Œ",        "á½ƒ" => "á½‹",
        "á½‚" => "á½Š",        "á½" => "á½‰",        "á½€" => "á½ˆ",
        "á¼·" => "á¼¿",        "á¼¶" => "á¼¾",        "á¼µ" => "á¼½",
        "á¼´" => "á¼¼",        "á¼³" => "á¼»",        "á¼²" => "á¼º",
        "á¼±" => "á¼¹",        "á¼°" => "á¼¸",        "á¼§" => "á¼¯",
        "á¼¦" => "á¼®",        "á¼¥" => "á¼­",        "á¼¤" => "á¼¬",
        "á¼£" => "á¼«",        "á¼¢" => "á¼ª",        "á¼¡" => "á¼©",
        "á¼ " => "á¼¨",        "á¼•" => "á¼",        "á¼”" => "á¼œ",
        "á¼“" => "á¼›",        "á¼’" => "á¼š",        "á¼‘" => "á¼™",
        "á¼" => "á¼˜",        "á¼‡" => "á¼",        "á¼†" => "á¼Ž",
        "á¼…" => "á¼",        "á¼„" => "á¼Œ",        "á¼ƒ" => "á¼‹",
        "á¼‚" => "á¼Š",        "á¼" => "á¼‰",        "á¼€" => "á¼ˆ",
        "á»¹" => "á»¸",        "á»·" => "á»¶",        "á»µ" => "á»´",
        "á»³" => "á»²",        "á»±" => "á»°",        "á»¯" => "á»®",
        "á»­" => "á»¬",        "á»«" => "á»ª",        "á»©" => "á»¨",
        "á»§" => "á»¦",        "á»¥" => "á»¤",        "á»£" => "á»¢",
        "á»¡" => "á» ",        "á»Ÿ" => "á»ž",        "á»" => "á»œ",
        "á»›" => "á»š",        "á»™" => "á»˜",        "á»—" => "á»–",
        "á»•" => "á»”",        "á»“" => "á»’",        "á»‘" => "á»",
        "á»" => "á»Ž",        "á»" => "á»Œ",        "á»‹" => "á»Š",
        "á»‰" => "á»ˆ",        "á»‡" => "á»†",        "á»…" => "á»„",
        "á»ƒ" => "á»‚",        "á»" => "á»€",        "áº¿" => "áº¾",
        "áº½" => "áº¼",        "áº»" => "áºº",        "áº¹" => "áº¸",
        "áº·" => "áº¶",        "áºµ" => "áº´",        "áº³" => "áº²",
        "áº±" => "áº°",        "áº¯" => "áº®",        "áº­" => "áº¬",
        "áº«" => "áºª",        "áº©" => "áº¨",        "áº§" => "áº¦",
        "áº¥" => "áº¤",        "áº£" => "áº¢",        "áº¡" => "áº ",
        "áº›" => "á¹ ",        "áº•" => "áº”",        "áº“" => "áº’",
        "áº‘" => "áº",        "áº" => "áºŽ",        "áº" => "áºŒ",
        "áº‹" => "áºŠ",        "áº‰" => "áºˆ",        "áº‡" => "áº†",
        "áº…" => "áº„",        "áºƒ" => "áº‚",        "áº" => "áº€",
        "á¹¿" => "á¹¾",        "á¹½" => "á¹¼",        "á¹»" => "á¹º",
        "á¹¹" => "á¹¸",        "á¹·" => "á¹¶",        "á¹µ" => "á¹´",
        "á¹³" => "á¹²",        "á¹±" => "á¹°",        "á¹¯" => "á¹®",
        "á¹­" => "á¹¬",        "á¹«" => "á¹ª",        "á¹©" => "á¹¨",
        "á¹§" => "á¹¦",        "á¹¥" => "á¹¤",        "á¹£" => "á¹¢",
        "á¹¡" => "á¹ ",        "á¹Ÿ" => "á¹ž",        "á¹" => "á¹œ",
        "á¹›" => "á¹š",        "á¹™" => "á¹˜",        "á¹—" => "á¹–",
        "á¹•" => "á¹”",        "á¹“" => "á¹’",        "á¹‘" => "á¹",
        "á¹" => "á¹Ž",         "á¹" => "á¹Œ",         "á¹‹" => "á¹Š",
        "á¹‰" => "á¹ˆ",        "á¹‡" => "á¹†",        "á¹…" => "á¹„",
        "á¹ƒ" => "á¹‚",        "á¹" => "á¹€",         "á¸¿" => "á¸¾",
        "á¸½" => "á¸¼",        "á¸»" => "á¸º",        "á¸¹" => "á¸¸",
        "á¸·" => "á¸¶",        "á¸µ" => "á¸´",        "á¸³" => "á¸²",
        "á¸±" => "á¸°",        "á¸¯" => "á¸®",        "á¸­" => "á¸¬",
        "á¸«" => "á¸ª",        "á¸©" => "á¸¨",        "á¸§" => "á¸¦",
        "á¸¥" => "á¸¤",        "á¸£" => "á¸¢",        "á¸¡" => "á¸ ",
        "á¸Ÿ" => "á¸ž",        "á¸" => "á¸œ",        "á¸›" => "á¸š",
        "á¸™" => "á¸˜",        "á¸—" => "á¸–",        "á¸•" => "á¸”",
        "á¸“" => "á¸’",        "á¸‘" => "á¸",        "á¸" => "á¸Ž",
        "á¸" => "á¸Œ",        "á¸‹" => "á¸Š",        "á¸‰" => "á¸ˆ",
        "á¸‡" => "á¸†",        "á¸…" => "á¸„",        "á¸ƒ" => "á¸‚",
        "á¸" => "á¸€",        "Ö†" => "Õ–",         "Ö…" => "Õ•",
        "Ö„" => "Õ”",        "Öƒ" => "Õ“",        "Ö‚" => "Õ’",
        "Ö" => "Õ‘",        "Ö€" => "Õ",        "Õ¿" => "Õ",
        "Õ¾" => "ÕŽ",        "Õ½" => "Õ",        "Õ¼" => "ÕŒ",
        "Õ»" => "Õ‹",        "Õº" => "ÕŠ",        "Õ¹" => "Õ‰",
        "Õ¸" => "Õˆ",        "Õ·" => "Õ‡",        "Õ¶" => "Õ†",
        "Õµ" => "Õ…",        "Õ´" => "Õ„",        "Õ³" => "Õƒ",
        "Õ²" => "Õ‚",        "Õ±" => "Õ",        "Õ°" => "Õ€",
        "Õ¯" => "Ô¿",        "Õ®" => "Ô¾",        "Õ­" => "Ô½",
        "Õ¬" => "Ô¼",        "Õ«" => "Ô»",        "Õª" => "Ôº",
        "Õ©" => "Ô¹",        "Õ¨" => "Ô¸",        "Õ§" => "Ô·",
        "Õ¦" => "Ô¶",        "Õ¥" => "Ôµ",        "Õ¤" => "Ô´",
        "Õ£" => "Ô³",        "Õ¢" => "Ô²",        "Õ¡" => "Ô±",
        "Ô" => "ÔŽ",        "Ô" => "ÔŒ",        "Ô‹" => "ÔŠ",
        "Ô‰" => "Ôˆ",        "Ô‡" => "Ô†",        "Ô…" => "Ô„",
        "Ôƒ" => "Ô‚",        "Ô" => "Ô€",        "Ó¹" => "Ó¸",
        "Óµ" => "Ó´",        "Ó³" => "Ó²",        "Ó±" => "Ó°",
        "Ó¯" => "Ó®",        "Ó­" => "Ó¬",        "Ó«" => "Óª",
        "Ó©" => "Ó¨",        "Ó§" => "Ó¦",        "Ó¥" => "Ó¤",
        "Ó£" => "Ó¢",        "Ó¡" => "Ó ",        "ÓŸ" => "Óž",
        "Ó" => "Óœ",        "Ó›" => "Óš",        "Ó™" => "Ó˜",
        "Ó—" => "Ó–",        "Ó•" => "Ó”",        "Ó“" => "Ó’",
        "Ó‘" => "Ó",        "ÓŽ" => "Ó",        "ÓŒ" => "Ó‹",
        "ÓŠ" => "Ó‰",        "Óˆ" => "Ó‡",        "Ó†" => "Ó…",
        "Ó„" => "Óƒ",        "Ó‚" => "Ó",        "Ò¿" => "Ò¾",
        "Ò½" => "Ò¼",        "Ò»" => "Òº",        "Ò¹" => "Ò¸",
        "Ò·" => "Ò¶",        "Òµ" => "Ò´",        "Ò³" => "Ò²",
        "Ò±" => "Ò°",        "Ò¯" => "Ò®",        "Ò­" => "Ò¬",
        "Ò«" => "Òª",        "Ò©" => "Ò¨",        "Ò§" => "Ò¦",
        "Ò¥" => "Ò¤",        "Ò£" => "Ò¢",        "Ò¡" => "Ò ",
        "ÒŸ" => "Òž",        "Ò" => "Òœ",        "Ò›" => "Òš",
        "Ò™" => "Ò˜",        "Ò—" => "Ò–",        "Ò•" => "Ò”",
        "Ò“" => "Ò’",        "Ò‘" => "Ò",        "Ò" => "ÒŽ",
        "Ò" => "ÒŒ",        "Ò‹" => "ÒŠ",        "Ò" => "Ò€",
        "Ñ¿" => "Ñ¾",        "Ñ½" => "Ñ¼",        "Ñ»" => "Ñº",
        "Ñ¹" => "Ñ¸",        "Ñ·" => "Ñ¶",        "Ñµ" => "Ñ´",
        "Ñ³" => "Ñ²",        "Ñ±" => "Ñ°",        "Ñ¯" => "Ñ®",
        "Ñ­" => "Ñ¬",        "Ñ«" => "Ñª",        "Ñ©" => "Ñ¨",
        "Ñ§" => "Ñ¦",        "Ñ¥" => "Ñ¤",        "Ñ£" => "Ñ¢",
        "Ñ¡" => "Ñ ",        "ÑŸ" => "Ð",        "Ñž" => "ÐŽ",
        "Ñ" => "Ð",        "Ñœ" => "ÐŒ",        "Ñ›" => "Ð‹",
        "Ñš" => "ÐŠ",        "Ñ™" => "Ð‰",        "Ñ˜" => "Ðˆ",
        "Ñ—" => "Ð‡",        "Ñ–" => "Ð†",        "Ñ•" => "Ð…",
        "Ñ”" => "Ð„",        "Ñ“" => "Ðƒ",        "Ñ’" => "Ð‚",
        "Ñ‘" => "Ð",        "Ñ" => "Ð€",        "Ñ" => "Ð¯",
        "ÑŽ" => "Ð®",        "Ñ" => "Ð­",        "ÑŒ" => "Ð¬",
        "Ñ‹" => "Ð«",        "ÑŠ" => "Ðª",        "Ñ‰" => "Ð©",
        "Ñˆ" => "Ð¨",        "Ñ‡" => "Ð§",        "Ñ†" => "Ð¦",
        "Ñ…" => "Ð¥",        "Ñ„" => "Ð¤",        "Ñƒ" => "Ð£",
        "Ñ‚" => "Ð¢",        "Ñ" => "Ð¡",        "Ñ€" => "Ð ",
        "Ð¿" => "ÐŸ",        "Ð¾" => "Ðž",        "Ð½" => "Ð",
        "Ð¼" => "Ðœ",        "Ð»" => "Ð›",        "Ðº" => "Ðš",
        "Ð¹" => "Ð™",        "Ð¸" => "Ð˜",        "Ð·" => "Ð—",
        "Ð¶" => "Ð–",        "Ðµ" => "Ð•",        "Ð´" => "Ð”",
        "Ð³" => "Ð“",        "Ð²" => "Ð’",        "Ð±" => "Ð‘",
        "Ð°" => "Ð",        "Ïµ" => "Î•",        "Ï²" => "Î£",
        "Ï±" => "Î¡",        "Ï°" => "Îš",        "Ï¯" => "Ï®",
        "Ï­" => "Ï¬",        "Ï«" => "Ïª",        "Ï©" => "Ï¨",
        "Ï§" => "Ï¦",        "Ï¥" => "Ï¤",        "Ï£" => "Ï¢",
        "Ï¡" => "Ï ",        "ÏŸ" => "Ïž",        "Ï" => "Ïœ",
        "Ï›" => "Ïš",        "Ï™" => "Ï˜",        "Ï–" => "Î ",
        "Ï•" => "Î¦",        "Ï‘" => "Î˜",        "Ï" => "Î’",
        "ÏŽ" => "Î",        "Ï" => "ÎŽ",        "ÏŒ" => "ÎŒ",
        "Ï‹" => "Î«",        "ÏŠ" => "Îª",        "Ï‰" => "Î©",
        "Ïˆ" => "Î¨",        "Ï‡" => "Î§",        "Ï†" => "Î¦",
        "Ï…" => "Î¥",        "Ï„" => "Î¤",        "Ïƒ" => "Î£",
        "Ï‚" => "Î£",        "Ï" => "Î¡",        "Ï€" => "Î ",
        "Î¿" => "ÎŸ",        "Î¾" => "Îž",        "Î½" => "Î",
        "Î¼" => "Îœ",        "Î»" => "Î›",        "Îº" => "Îš",
        "Î¹" => "Î™",        "Î¸" => "Î˜",        "Î·" => "Î—",
        "Î¶" => "Î–",        "Îµ" => "Î•",        "Î´" => "Î”",
        "Î³" => "Î“",        "Î²" => "Î’",        "Î±" => "Î‘",
        "Î¯" => "ÎŠ",        "Î®" => "Î‰",        "Î­" => "Îˆ",
        "Î¬" => "Î†",        "Ê’" => "Æ·",        "Ê‹" => "Æ²",
        "ÊŠ" => "Æ±",        "Êˆ" => "Æ®",        "Êƒ" => "Æ©",
        "Ê€" => "Æ¦",        "Éµ" => "ÆŸ",        "É²" => "Æ",
        "É¯" => "Æœ",        "É©" => "Æ–",        "É¨" => "Æ—",
        "É£" => "Æ”",        "É " => "Æ“",        "É›" => "Æ",
        "É™" => "Æ",        "É—" => "ÆŠ",        "É–" => "Æ‰",
        "É”" => "Æ†",        "É“" => "Æ",        "È³" => "È²",
        "È±" => "È°",        "È¯" => "È®",        "È­" => "È¬",
        "È«" => "Èª",        "È©" => "È¨",        "È§" => "È¦",
        "È¥" => "È¤",        "È£" => "È¢",        "ÈŸ" => "Èž",
        "È" => "Èœ",        "È›" => "Èš",        "È™" => "È˜",
        "È—" => "È–",        "È•" => "È”",        "È“" => "È’",
        "È‘" => "È",        "È" => "ÈŽ",        "È" => "ÈŒ",
        "È‹" => "ÈŠ",        "È‰" => "Èˆ",        "È‡" => "È†",
        "È…" => "È„",        "Èƒ" => "È‚",        "È" => "È€",
        "Ç¿" => "Ç¾",        "Ç½" => "Ç¼",        "Ç»" => "Çº",
        "Ç¹" => "Ç¸",        "Çµ" => "Ç´",        "Ç³" => "Ç²",
        "Ç¯" => "Ç®",        "Ç­" => "Ç¬",        "Ç«" => "Çª",
        "Ç©" => "Ç¨",        "Ç§" => "Ç¦",        "Ç¥" => "Ç¤",
        "Ç£" => "Ç¢",        "Ç¡" => "Ç ",        "ÇŸ" => "Çž",
        "Ç" => "ÆŽ",        "Çœ" => "Ç›",        "Çš" => "Ç™",
        "Ç˜" => "Ç—",        "Ç–" => "Ç•",        "Ç”" => "Ç“",
        "Ç’" => "Ç‘",        "Ç" => "Ç",        "ÇŽ" => "Ç",
        "ÇŒ" => "Ç‹",        "Ç‰" => "Çˆ",        "Ç†" => "Ç…",
        "Æ¿" => "Ç·",        "Æ½" => "Æ¼",        "Æ¹" => "Æ¸",
        "Æ¶" => "Æµ",        "Æ´" => "Æ³",        "Æ°" => "Æ¯",
        "Æ­" => "Æ¬",        "Æ¨" => "Æ§",        "Æ¥" => "Æ¤",
        "Æ£" => "Æ¢",        "Æ¡" => "Æ ",        "Æž" => "È ",
        "Æ™" => "Æ˜",        "Æ•" => "Ç¶",        "Æ’" => "Æ‘",
        "ÆŒ" => "Æ‹",        "Æˆ" => "Æ‡",        "Æ…" => "Æ„",
        "Æƒ" => "Æ‚",        "Å¿" => "S",        "Å¾" => "Å½",
        "Å¼" => "Å»",        "Åº" => "Å¹",        "Å·" => "Å¶",
        "Åµ" => "Å´",        "Å³" => "Å²",        "Å±" => "Å°",
        "Å¯" => "Å®",        "Å­" => "Å¬",        "Å«" => "Åª",
        "Å©" => "Å¨",        "Å§" => "Å¦",        "Å¥" => "Å¤",
        "Å£" => "Å¢",        "Å¡" => "Å ",        "ÅŸ" => "Åž",
        "Å" => "Åœ",        "Å›" => "Åš",        "Å™" => "Å˜",
        "Å—" => "Å–",        "Å•" => "Å”",        "Å“" => "Å’",
        "Å‘" => "Å",        "Å" => "ÅŽ",        "Å" => "ÅŒ",
        "Å‹" => "ÅŠ",        "Åˆ" => "Å‡",        "Å†" => "Å…",
        "Å„" => "Åƒ",        "Å‚" => "Å",        "Å€" => "Ä¿",
        "Ä¾" => "Ä½",        "Ä¼" => "Ä»",        "Äº" => "Ä¹",
        "Ä·" => "Ä¶",        "Äµ" => "Ä´",        "Ä³" => "Ä²",
        "Ä±" => "I",        "Ä¯" => "Ä®",        "Ä­" => "Ä¬",
        "Ä«" => "Äª",        "Ä©" => "Ä¨",        "Ä§" => "Ä¦",
        "Ä¥" => "Ä¤",        "Ä£" => "Ä¢",        "Ä¡" => "Ä ",
        "ÄŸ" => "Äž",        "Ä" => "Äœ",        "Ä›" => "Äš",
        "Ä™" => "Ä˜",        "Ä—" => "Ä–",        "Ä•" => "Ä”",
        "Ä“" => "Ä’",        "Ä‘" => "Ä",        "Ä" => "ÄŽ",
        "Ä" => "ÄŒ",        "Ä‹" => "ÄŠ",        "Ä‰" => "Äˆ",
        "Ä‡" => "Ä†",        "Ä…" => "Ä„",        "Äƒ" => "Ä‚",
        "Ä" => "Ä€",        "Ã¿" => "Å¸",        "Ã¾" => "Ãž",
        "Ã½" => "Ã",        "Ã¼" => "Ãœ",        "Ã»" => "Ã›",
        "Ãº" => "Ãš",        "Ã¹" => "Ã™",        "Ã¸" => "Ã˜",
        "Ã¶" => "Ã–",        "Ãµ" => "Ã•",        "Ã´" => "Ã”",
        "Ã³" => "Ã“",        "Ã²" => "Ã’",        "Ã±" => "Ã‘",
        "Ã°" => "Ã",        "Ã¯" => "Ã",        "Ã®" => "ÃŽ",
        "Ã­" => "Ã",        "Ã¬" => "ÃŒ",        "Ã«" => "Ã‹",
        "Ãª" => "ÃŠ",        "Ã©" => "Ã‰",        "Ã¨" => "Ãˆ",
        "Ã§" => "Ã‡",        "Ã¦" => "Ã†",        "Ã¥" => "Ã…",
        "Ã¤" => "Ã„",        "Ã£" => "Ãƒ",        "Ã¢" => "Ã‚",
        "Ã¡" => "Ã",        "Ã " => "Ã€",        "Âµ" => "Îœ",
        "z" => "Z",          "y" => "Y",          "x" => "X",
        "w" => "W",          "v" => "V",          "u" => "U",
        "t" => "T",          "s" => "S",          "r" => "R",
        "q" => "Q",          "p" => "P",          "o" => "O",
        "n" => "N",          "m" => "M",          "l" => "L",
        "k" => "K",          "j" => "J",          "i" => "I",
        "h" => "H",          "g" => "G",          "f" => "F",
        "e" => "E",          "d" => "D",          "c" => "C",
        "b" => "B",          "a" => "A",
);

?>