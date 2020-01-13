<?php

require_once 'vendor/autoload.php';
require_once 'ParsedownFilter.class.php';

$parsedown = new ParsedownFilter(function (&$el) {
	switch ($el['name']) {
		case 'a':
			$url = $el['attributes']['href'];

			if (strpos($url, '://') === false)
				if ((($url[0] == '/') && ($url[1] != '/')) || ($url[0] != '/')) return;

			if ($url[0] == '#') return;

			$el['attributes']['target'] = '_blank';
			break;

		case 'h1':
		case 'h2':
		case 'h3':
		case 'h4':
		case 'h5':
		case 'h6':
			$el['attributes']['id'] = str_replace(' ', '-', strtolower($el['text']));
			break;
	}
});
$parsedown->setUrlsLinked(false)
          ->setBreaksEnabled(false)
          ->setMarkupEscaped(false)
          ->setSafeMode(false);

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
	$docsHtml = null;
	$docsVisibility = 'hidden';

	if (!empty($plugin->docs)) {
		$docsUrl = $plugin->docs;

		$urlParts = parse_url($docsUrl);
		if ($urlParts['host'] == 'github.com' && substr($urlParts['path'], -2) == 'md') {
			$downloadUrl = 'https://raw.githubusercontent.com' . str_replace('/blob', '', $urlParts['path']);

			echo PHP_EOL . "\t> Downloading plugin docs for {$plugin->handle} from $downloadUrl... ";

			$rawMarkdown = file_get_contents($downloadUrl);

			if (!$rawMarkdown)
				die('Failed!' . PHP_EOL);

			echo 'Done.' . PHP_EOL;

			echo "\t> Generating {$plugin->handle}/docs/index.html out of _templates/_docs.html... ";

			$docsHtmlTemplate = file_get_contents('_templates/_docs.html');

			if (!$docsHtmlTemplate)
				die('Failed!' . PHP_EOL);

			$docsHtml = $docsHtmlTemplate;
			$docsHtml = preg_replace('/\{\{ *content *\}\}/', $parsedown->text($rawMarkdown), $docsHtml);
			$docsHtml = preg_replace('/\{\{ *name *\}\}/', $plugin->name, $docsHtml);
			$docsHtml = preg_replace('/\{\{ *handle *\}\}/', $plugin->handle, $docsHtml);

			echo 'Done.' . PHP_EOL;

			$docsUrl = "/{$plugin->handle}/docs";
			$docsVisibility = 'shown';
		}

		$pluginHtml = preg_replace('/\{\{ *docs *\}\}/', $docsUrl, $pluginHtml);
	}

	$pluginHtml = preg_replace('/\{\{ *docsVisibility *\}\}/', $docsVisibility, $pluginHtml);

	foreach (array_keys(get_object_vars($plugin)) as $key) {
		$pluginHtml = preg_replace('/\{\{ *' . $key . ' *\}\}/', $plugin->{$key}, $pluginHtml);
	}

	if (!is_dir($plugin->handle) && !mkdir($plugin->handle))
		die('Failed!' . PHP_EOL);

	if (!file_put_contents("{$plugin->handle}/index.html", $pluginHtml))
		die('Failed!' . PHP_EOL);

	if ($docsHtml) {
		if (!is_dir($plugin->handle . '/docs') && !mkdir($plugin->handle . '/docs'))
			die('Failed!' . PHP_EOL);

		if (!file_put_contents("{$plugin->handle}/docs/index.html", $docsHtml))
			die('Failed!' . PHP_EOL);
	}

	echo 'Done.' . PHP_EOL;
}

echo PHP_EOL . PHP_EOL . PHP_EOL;