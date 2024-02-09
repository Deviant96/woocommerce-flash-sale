<?php
if (!defined('ABSPATH')) {
    exit;
}


/**
 * The below function will help to load template file from plugin directory of wordpress
 *  Extracted from : http://wordpress.stackexchange.com/questions/94343/get-template-part-from-plugin
 */
function lk6_get_template_part($slug, $name = null)
{

    do_action("lk6_get_template_part_{$slug}", $slug, $name);

    $templates = array();
    if (isset($name)) {
        $templates[] = "{$slug}-{$name}.php";
    }

    $templates[] = "{$slug}.php";
    // var_dump(lk6_get_template_path($templates, true, false));
    lk6_get_template_path($templates, true, false);
}

/* Extend locate_template from WP Core
 * Define a location of your plugin file dir to a constant in this case = PLUGIN_DIR_PATH
 * Note: PLUGIN_DIR_PATH - can be any folder/subdirectory within your plugin files
 */
function lk6_get_template_path($template_names, $load = false, $require_once = true)
{
    $located = '';
    // var_dump(PLUGIN_DIR_PATH);
    // echo "template path";
    foreach ((array) $template_names as $template_name) {
        // echo $template_name;
        if (!$template_name) {
            continue;
        }

        /* search file within the PLUGIN_DIR_PATH only */
        if (file_exists(PLUGIN_DIR . $template_name)) {
            // echo "ada";
            $located = PLUGIN_DIR . $template_name;
            // echo $located;
            break;
        }
    }

    if ($load && '' != $located) {
        load_template($located, $require_once);
    }

    return $located;
}


/**
 * Truncate text by characters
 *
 * @param $text String - text to truncate
 * @param $chars Integer - number of characters to truncate to - default 40
 * @param $breakWord Boolean - if true, will break on word boundaries - when false, could lead to strings longer than $chars
 * @param $ellipsis String - if set, will append to truncated text, '…' character by default
 */
function truncateTextByChars(
    $text,
    $chars = 40,
    $breakWord = false,
    $ellipsis = '…') {
    if (empty($text)) {
        return null;
    }

    if ($breakWord) {
        $truncate = substr($text, 0, $chars);
        return $ellipsis && strlen($truncate) < strlen($text)
        ? $truncate . $ellipsis
        : $truncate;
    }

    if (strlen($text) > $chars) {
        $shortened = (substr($text, 0, strpos($text, ' ', $chars)));
        $final = $ellipsis && strlen($shortened) > 0
        ? $shortened . $ellipsis
        : $text;
    } else {
        $final = $text;
    }
    return $final;
}