# GitUpdater

PHP GitUpdater for actualization your own projects. It works on latest version repository release. Downloads and replaces old files new ones.

#### Simple example

```php
<?php
  DEFINE("DS", DIRECTORY_SEPARATOR);
  DEFINE("PROJEKT_PLUGIN_FILE", __DIR__ . DS);
  DEFINE("ROOT", PROJEKT_PLUGIN_FILE . DS);
  DEFINE("DOWNLOAD_PATH", ROOT . "downloads" . DS);

  require_once("GitUpdater.class.php");

  $gitUpdater = new GitUpdater();
  $gitUpdater->GitConnect('Git2', 'marcinl13');
  $gitUpdater->LocalData('info.json');
  $gitUpdater->DownloadFile(DOWNLOAD_PATH);
  $gitUpdater->UnZip(DOWNLOAD_PATH);

  //print errors
  print_r($gitUpdater->ShowErrors());
?>
```
