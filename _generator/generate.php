<?php

echo PHP_EOL . PHP_EOL . '<< SkayoCrafts Website - Static Site Generator >>' . PHP_EOL . PHP_EOL;

echo '> Reading _plugins.xml... ';

$plugins = simplexml_load_file('_plugins.xml');

if (!$plugins)
	die('Failed!' . PHP_EOL);

echo 'Done.' . PHP_EOL;

echo '> Making plugin list for _templates/_index.html... ';

$pluginListHtml = '';

foreach ($plugins->children() as $plugin) {
	$pluginListHtml .=
		'<li>' .
		"<a class=\"plugin-link\" title=\"Go to plugin page\" href=\"/{$plugin->handle}\">{$plugin->name}</a>" .
		'</li>';
}

echo 'Done.' . PHP_EOL;

echo '> Generating index.html out of _templates/_index.html... ';

$indexHtmlTemplate = file_get_contents('_templates/_index.html');

if (!$indexHtmlTemplate)
	die('Failed!' . PHP_EOL);

$indexHtml = preg_replace('/\{\{ *plugin-list *\}\}/', $pluginListHtml, $indexHtmlTemplate);

if (!file_put_contents('index.html', $indexHtml))
	die('Failed!' . PHP_EOL);

echo 'Done.' . PHP_EOL;

foreach ($plugins->children() as $plugin) {
	echo "> Generating {$plugin->handle}/index.html out of _templates/_plugin.html... ";

	$pluginHtmlTemplate = file_get_contents('_templates/_plugin.html');

	if (!$pluginHtmlTemplate)
		die('Failed!' . PHP_EOL);

	$pluginHtml = $pluginHtmlTemplate;

	foreach (array_keys(get_object_vars($plugin)) as $key) {
		$pluginHtml = preg_replace('/\{\{ *' . $key . ' *\}\}/', $plugin->{$key}, $pluginHtml);
	}

	if (!is_dir($plugin->handle) && !mkdir($plugin->handle))
		die('Failed!' . PHP_EOL);

	if (!file_put_contents("{$plugin->handle}/index.html", $pluginHtml))
		die('Failed!' . PHP_EOL);

	echo 'Done.' . PHP_EOL;
}

echo PHP_EOL . PHP_EOL . PHP_EOL;