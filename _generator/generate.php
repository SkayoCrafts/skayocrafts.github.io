<?php

echo PHP_EOL . PHP_EOL . '<< SkayoCrafts Website - Static Site Generator >>' . PHP_EOL . PHP_EOL;

echo 'Working dir: ' . getcwd() . PHP_EOL . PHP_EOL;

echo '> Reading _plugins.json... ';

$rawJson = file_get_contents('./_plugins.json');

if (!$rawJson)
	die('Failed!' . PHP_EOL);

echo 'Done.' . PHP_EOL;

echo '> Decoding JSON... ';

$plugins = json_decode($rawJson, true);

if (is_null($plugins))
	die('Failed!' . PHP_EOL);

echo 'Done.' . PHP_EOL;

echo '> Making plugin list for _templates/_index.html... ';

$pluginListHtml = '';

foreach ($plugins as $plugin) {
	$pluginListHtml .=
		'<li>' .
		"<a title=\"Go to plugin page\" href=\"/{$plugin['handle']}\" target=\"_blank\">{$plugin['name']}</a>" .
		'</li>';
}

echo 'Done.' . PHP_EOL;

echo '> Generating index.html out of _templates/_index.html... ';

$indexHtmlTemplate = file_get_contents('./_templates/_index.html...');

if (!$indexHtmlTemplate)
	die('Failed!' . PHP_EOL);

$indexHtml = preg_replace('/\{\{ *plugin_list *\}\}/', $pluginListHtml, $indexHtmlTemplate);

if (!file_put_contents('index.html', $indexHtml))
	die('Failed!' . PHP_EOL);

echo 'Done.' . PHP_EOL;

foreach ($plugins as $plugin) {
	echo "> Generating {$plugin['handle']}/index.html out of _templates/_plugin.html... ";

	$pluginHtmlTemplate = file_get_contents('./_templates/_index.html');

	if (!$pluginHtmlTemplate)
		die('Failed!' . PHP_EOL);

	$pluginHtml = $pluginHtmlTemplate;

	foreach (array_keys($plugin) as $key) {
		$pluginHtml = preg_replace('/\{\{ *' . $key . ' *\}\}/', $plugin[$key], $pluginHtml);
	}

	if (!is_dir($plugin['handle']) && !mkdir($plugin['handle']))
		die('Failed!' . PHP_EOL);

	if (!file_put_contents("{$plugin['handle']}/index.html", $pluginHtml))
		die('Failed!' . PHP_EOL);

	echo 'Done.' . PHP_EOL;
}

echo PHP_EOL . PHP_EOL . PHP_EOL;